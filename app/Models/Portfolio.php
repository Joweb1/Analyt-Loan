<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Borrower> $borrowers
 * @property-read int|null $borrowers_count
 * @property-read float $par_percentage
 * @property-read \App\ValueObjects\Money $portfolio_at_risk
 * @property-read \App\ValueObjects\Money $portfolio_balance
 * @property-read \App\ValueObjects\Money $profit_loss
 * @property-read \App\ValueObjects\Money $savings_balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Loan> $loans
 * @property-read int|null $loans_count
 * @property-read \App\Models\Organization $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $staff
 * @property-read int|null $staff_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Portfolio extends Model
{
    use BelongsToOrganization, HasFactory, HasUuids;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
    ];

    public function borrowers(): HasMany
    {
        return $this->hasMany(Borrower::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'portfolio_user');
    }

    /**
     * Total Portfolio Balance (Lending): total loaned + interest + fees - repayments
     */
    public function getPortfolioBalanceAttribute(): \App\ValueObjects\Money
    {
        // Get all loans that are not drafted, rejected or pending application
        $loans = $this->loans()->whereNotIn('status', ['draft', 'rejected', 'applied'])->get();
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');

        $totalValueMinor = 0;
        foreach ($loans as $loan) {
            /** @var \App\Models\Loan $loan */
            $totalValueMinor += $loan->getTotalCost()->getMinorAmount();
        }

        $totalCollectedMinor = (int) Repayment::whereIn('loan_id', $loans->pluck('id'))->sum('amount');

        return new \App\ValueObjects\Money($totalValueMinor - $totalCollectedMinor, $currency);
    }

    /**
     * Total Portfolio Savings Amount
     */
    public function getSavingsBalanceAttribute(): \App\ValueObjects\Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        // Corrected to use user_id from the linked borrowers
        $userIds = $this->borrowers()->pluck('user_id');
        $totalMinor = (int) SavingsAccount::whereIn('user_id', $userIds)->sum('balance');

        return new \App\ValueObjects\Money($totalMinor, $currency);
    }

    /**
     * Portfolio At Risk (PAR): Entire outstanding principal of loans with overdue installments.
     */
    public function getPortfolioAtRiskAttribute(): \App\ValueObjects\Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        // Standard PAR: Outstanding principal of any loan that has an installment overdue
        $overdueLoanIds = ScheduledRepayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->where('status', 'overdue')
            ->pluck('loan_id')
            ->unique();

        $totalPAR = new \App\ValueObjects\Money(0, $currency);
        $loans = Loan::whereIn('id', $overdueLoanIds)->get();

        foreach ($loans as $loan) {
            $totalPaidPrincipalMinor = (int) $loan->repayments()->sum('principal_amount');
            $totalPaidPrincipal = new \App\ValueObjects\Money($totalPaidPrincipalMinor, $currency);
            $totalPAR = $totalPAR->add($loan->amount->subtract($totalPaidPrincipal));
        }

        return $totalPAR;
    }

    /**
     * PAR Percentage: (PAR / Portfolio Balance) * 100
     */
    public function getParPercentageAttribute(): float
    {
        $balance = $this->portfolio_balance;
        if ($balance->isZero()) {
            return 0;
        }

        return round(($this->portfolio_at_risk->getMajorAmount() / $balance->getMajorAmount()) * 100, 2);
    }

    /**
     * Profit and Loss (PnL): Interest Collected - Principal of Loans Overdue > 7 days
     */
    public function getProfitLossAttribute(): \App\ValueObjects\Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        // Profit = Total Interest Collected
        $totalInterestCollectedMinor = (int) Repayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->sum('interest_amount');
        $totalInterestCollected = new \App\ValueObjects\Money($totalInterestCollectedMinor, $currency);

        // Loss = Principal of Loans Overdue > 7 days
        $defaultedLoanIds = ScheduledRepayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->where('status', 'overdue')
            ->where('due_date', '<=', now()->subDays(7))
            ->pluck('loan_id')
            ->unique();

        $totalLossPrincipal = new \App\ValueObjects\Money(0, $currency);
        $loans = Loan::whereIn('id', $defaultedLoanIds)->get();

        foreach ($loans as $loan) {
            $totalPaidPrincipalMinor = (int) $loan->repayments()->sum('principal_amount');
            $totalPaidPrincipal = new \App\ValueObjects\Money($totalPaidPrincipalMinor, $currency);
            $totalLossPrincipal = $totalLossPrincipal->add($loan->amount->subtract($totalPaidPrincipal));
        }

        return $totalInterestCollected->subtract($totalLossPrincipal);
    }
}

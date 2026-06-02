<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $organization_id
 * @property string $name
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Borrower> $borrowers
 * @property-read int|null $borrowers_count
 * @property-read float $par_percentage
 * @property-read Money $portfolio_at_risk
 * @property-read Money $portfolio_balance
 * @property-read Money $profit_loss
 * @property-read Money $savings_balance
 * @property-read Collection<int, Loan> $loans
 * @property-read int|null $loans_count
 * @property-read Organization $organization
 * @property-read Collection<int, User> $staff
 * @property-read int|null $staff_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Portfolio whereUpdatedAt($value)
 *
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
    public function getPortfolioBalanceAttribute(): Money
    {
        // Get all loans that are not drafted, rejected or pending application
        $loans = $this->loans()->whereNotIn('status', ['draft', 'rejected', 'applied'])->get();
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');

        $totalValueMinor = 0;
        foreach ($loans as $loan) {
            /** @var Loan $loan */
            $totalValueMinor += $loan->getTotalCost()->getMinorAmount();
        }

        $totalCollectedMinor = (int) Repayment::whereIn('loan_id', $loans->pluck('id'))->sum('amount');

        return new Money($totalValueMinor - $totalCollectedMinor, $currency);
    }

    /**
     * Total Portfolio Savings Amount
     */
    public function getSavingsBalanceAttribute(): Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        // Corrected to use user_id from the linked borrowers
        $userIds = $this->borrowers()->pluck('user_id');
        $totalMinor = (int) SavingsAccount::whereIn('user_id', $userIds)->sum('balance');

        return new Money($totalMinor, $currency);
    }

    /**
     * Portfolio At Risk (PAR): Entire outstanding principal of loans with overdue installments.
     */
    public function getPortfolioAtRiskAttribute(): Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        // Standard PAR: Outstanding principal of any loan that has an installment overdue
        $overdueLoanIds = ScheduledRepayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->where('status', 'overdue')
            ->pluck('loan_id')
            ->unique();

        $totalPAR = new Money(0, $currency);
        $loans = Loan::whereIn('id', $overdueLoanIds)->get();

        foreach ($loans as $loan) {
            $totalPaidPrincipalMinor = (int) $loan->repayments()->sum('principal_amount');
            $totalPaidPrincipal = new Money($totalPaidPrincipalMinor, $currency);
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
    public function getProfitLossAttribute(): Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        // Profit = Total Interest Collected
        $totalInterestCollectedMinor = (int) Repayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->sum('interest_amount');
        $totalInterestCollected = new Money($totalInterestCollectedMinor, $currency);

        // Loss = Principal of Loans Overdue > 7 days
        $defaultedLoanIds = ScheduledRepayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->where('status', 'overdue')
            ->where('due_date', '<=', now()->subDays(7))
            ->pluck('loan_id')
            ->unique();

        $totalLossPrincipal = new Money(0, $currency);
        $loans = Loan::whereIn('id', $defaultedLoanIds)->get();

        foreach ($loans as $loan) {
            $totalPaidPrincipalMinor = (int) $loan->repayments()->sum('principal_amount');
            $totalPaidPrincipal = new Money($totalPaidPrincipalMinor, $currency);
            $totalLossPrincipal = $totalLossPrincipal->add($loan->amount->subtract($totalPaidPrincipal));
        }

        return $totalInterestCollected->subtract($totalLossPrincipal);
    }
}

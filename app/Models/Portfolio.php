<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Total Portfolio Balance (Lending): total loaned + interest - repayments
     */
    public function getPortfolioBalanceAttribute(): \App\ValueObjects\Money
    {
        // Get all loans that are not drafted, rejected or pending application
        $loans = $this->loans()->whereNotIn('status', ['draft', 'rejected', 'applied'])->get();
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');

        $totalLoaned = new \App\ValueObjects\Money(0, $currency);
        foreach ($loans as $loan) {
            /** @var \App\Models\Loan $loan */
            $totalLoaned = $totalLoaned->add($loan->amount ?? new \App\ValueObjects\Money(0, $currency));
        }

        $totalInterest = new \App\ValueObjects\Money(0, $currency);
        foreach ($loans as $loan) {
            /** @var \App\Models\Loan $loan */
            $totalInterest = $totalInterest->add($loan->getTotalExpectedInterest());
        }

        $totalCollectedMinor = (int) Repayment::whereIn('loan_id', $loans->pluck('id'))->sum('amount');
        $totalCollected = new \App\ValueObjects\Money($totalCollectedMinor, $currency);

        return $totalLoaned->add($totalInterest)->subtract($totalCollected);
    }

    /**
     * Total Portfolio Savings Amount
     */
    public function getSavingsBalanceAttribute(): \App\ValueObjects\Money
    {
        $currency = $this->organization->currency_code ?? config('app.currency', 'NGN');
        $totalMinor = (int) SavingsAccount::whereIn('borrower_id', $this->borrowers()->pluck('id'))->sum('balance');

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
            ->where('due_date', '<=', \App\Models\Organization::systemNow()->subDays(7))
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

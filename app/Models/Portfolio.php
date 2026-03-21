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
    public function getPortfolioBalanceAttribute(): float
    {
        $loans = $this->loans()->whereIn('status', ['active', 'overdue', 'repaid'])->get();

        $totalLoanedPlusInterest = $loans->sum(function ($loan) {
            /** @var Loan $loan */
            $totalInterest = (float) $loan->amount * (($loan->interest_rate ?? 0) / 100);

            return (float) $loan->amount + $totalInterest;
        });

        $totalCollected = $loans->sum(function ($loan) {
            /** @var Loan $loan */
            return (float) $loan->repayments()->sum('amount');
        });

        return round($totalLoanedPlusInterest - $totalCollected, 2);
    }

    /**
     * Total Portfolio Savings Amount
     */
    public function getSavingsBalanceAttribute(): float
    {
        return (float) $this->borrowers()->with('savingsAccount')->get()->sum(function ($borrower) {
            /** @var Borrower $borrower */
            return $borrower->savingsAccount ? (float) $borrower->savingsAccount->balance : 0;
        });
    }

    /**
     * Portfolio At Risk (PAR): Entire outstanding principal of loans with overdue installments.
     */
    public function getPortfolioAtRiskAttribute(): float
    {
        // Standard PAR: Outstanding principal of any loan that has an installment overdue
        $overdueLoanIds = ScheduledRepayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->where('status', 'overdue')
            ->pluck('loan_id')
            ->unique();

        return (float) Loan::whereIn('id', $overdueLoanIds)->get()->sum(function ($loan) {
            /** @var Loan $loan */
            $totalPaidPrincipal = $loan->repayments()->sum('principal_amount');

            return max(0, (float) $loan->amount - (float) $totalPaidPrincipal);
        });
    }

    /**
     * PAR Percentage: (PAR / Portfolio Balance) * 100
     */
    public function getParPercentageAttribute(): float
    {
        $balance = $this->portfolio_balance;
        if ($balance <= 0) {
            return 0;
        }

        return round(($this->portfolio_at_risk / $balance) * 100, 2);
    }

    /**
     * Profit and Loss (PnL): Interest Collected - Principal of Loans Overdue > 7 days
     */
    public function getProfitLossAttribute(): float
    {
        // Profit = Total Interest Collected
        $totalInterestCollected = Repayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->sum('interest_amount');

        // Loss = Principal of Loans Overdue > 7 days
        $defaultedLoanIds = ScheduledRepayment::whereIn('loan_id', $this->loans()->pluck('id'))
            ->where('status', 'overdue')
            ->where('due_date', '<=', now()->subDays(7))
            ->pluck('loan_id')
            ->unique();

        $totalLossPrincipal = Loan::whereIn('id', $defaultedLoanIds)->get()->sum(function ($loan) {
            /** @var Loan $loan */
            $totalPaidPrincipal = $loan->repayments()->sum('principal_amount');

            return max(0, (float) $loan->amount - (float) $totalPaidPrincipal);
        });

        return round($totalInterestCollected - $totalLossPrincipal, 2);
    }
}

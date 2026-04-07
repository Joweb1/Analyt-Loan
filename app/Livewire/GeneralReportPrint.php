<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GeneralReportPrint extends Component
{
    public $type;

    public $title;

    public $startDate;

    public $endDate;

    public $metrics = [];

    public $chartData = [];

    public $staffPerformance = [];

    public $organization;

    public $generatedBy;

    public function mount($type = 'daily')
    {
        $this->type = $type;
        $this->organization = Auth::user()->organization;
        $this->generatedBy = Auth::user();
        $this->calculatePeriod();
        $this->fetchData();
    }

    protected function calculatePeriod()
    {
        $now = now();
        match ($this->type) {
            'daily' => [
                $this->startDate = $now->copy()->startOfDay(),
                $this->endDate = $now->copy()->endOfDay(),
                $this->title = 'Daily Performance Report',
            ],
            'weekly' => [
                $this->startDate = $now->copy()->startOfWeek(),
                $this->endDate = $now->copy()->endOfWeek(),
                $this->title = 'Weekly Operations Report',
            ],
            'monthly' => [
                $this->startDate = $now->copy()->startOfMonth(),
                $this->endDate = $now->copy()->endOfMonth(),
                $this->title = 'Monthly Business Review',
            ],
            'yearly' => [
                $this->startDate = $now->copy()->startOfYear(),
                $this->endDate = $now->copy()->endOfYear(),
                $this->title = 'Annual Financial Report',
            ],
            default => [
                $this->startDate = $now->copy()->startOfDay(),
                $this->endDate = $now->copy()->endOfDay(),
                $this->title = 'Organization Report',
            ]
        };
    }

    protected function fetchData()
    {
        $orgId = $this->organization->id;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        // 1. Total Disbursed (Period) - Using fallback logic
        $this->metrics['disbursed'] = (float) Loan::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('release_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->whereNull('release_date')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->sum('amount');

        // 2. Total Loans Count (Period)
        $this->metrics['totalLoansCount'] = Loan::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('release_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->whereNull('release_date')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->count();

        // 3. Collected in Period
        $this->metrics['collected'] = (float) Repayment::whereHas('loan', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        // 4. New Customers in Period
        $this->metrics['newCustomers'] = Borrower::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // 5. Net Savings Growth in Period
        $savingsDeposits = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })
            ->where('type', 'deposit')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $savingsWithdrawals = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })
            ->where('type', 'withdrawal')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $this->metrics['totalSavings'] = (float) ($savingsDeposits - $savingsWithdrawals);

        // 6. Total Expected Interest (LIFETIME)
        $this->metrics['totalInterest'] = (float) Loan::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
            ->get()
            ->reduce(function ($carry, $loan) {
                return $carry + $loan->getTotalExpectedInterest();
            }, 0.0);

        // 7. Total Paid Interest (LIFETIME)
        $this->metrics['totalPaidInterest'] = (float) Repayment::whereHas('loan', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })->sum('interest_amount');

        $this->metrics['remainingInterest'] = max(0, $this->metrics['totalInterest'] - $this->metrics['totalPaidInterest']);

        // 8. Portfolio at Risk (Loans that became overdue in period)
        $overdueLoanIds = ScheduledRepayment::where('status', 'overdue')
            ->whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereBetween('due_date', [$startDate, $endDate])
            ->pluck('loan_id')
            ->unique();

        $this->metrics['totalPAR'] = Loan::whereIn('id', $overdueLoanIds)->get()->sum(function ($loan) {
            $totalPaidPrincipal = $loan->repayments()->sum('principal_amount');

            return max(0, (float) $loan->amount - (float) $totalPaidPrincipal);
        });

        // 9. Profit & Loss (PnL) in Period
        $periodPaidInterest = (float) Repayment::whereHas('loan', function ($q) use ($orgId) {
            $q->where('organization_id', $orgId);
        })
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('interest_amount');

        $totalFeesPeriod = (float) Loan::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('release_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->whereNull('release_date')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->sum(\Illuminate\Support\Facades\DB::raw('processing_fee + insurance_fee'));

        $this->metrics['totalPnL'] = $periodPaidInterest + $totalFeesPeriod;

        // 10. Organization Balance (Snapshot)
        $this->metrics['orgBalance'] = Loan::where('organization_id', $orgId)
            ->whereIn('status', ['approved', 'active', 'overdue'])
            ->get()
            ->sum(function ($loan) {
                return $loan->balance;
            });

        // 11. Staff Performance (Top 5 by Collection)
        $staffUsers = User::where('organization_id', $orgId)
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('name', ['Borrower']);
            })
            ->get();

        foreach ($staffUsers as $user) {
            $collected = Repayment::where('collected_by', $user->id)
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('amount');

            if ($collected > 0) {
                $this->staffPerformance[] = [
                    'name' => $user->name,
                    'role' => $user->getRoleNames()->first() ?? 'Staff',
                    'collected' => $collected,
                ];
            }
        }

        usort($this->staffPerformance, fn ($a, $b) => $b['collected'] <=> $a['collected']);
        $this->staffPerformance = array_slice($this->staffPerformance, 0, 5);

        // Chart Data (using the same logic as Reports.php)
        $this->chartData = $this->getChartData($orgId);
    }

    protected function getChartData($orgId)
    {
        $labels = [];
        $disbursedData = [];
        $collectedData = [];
        $interestExpectedData = [];
        $interestPaidData = [];
        $customerData = [];
        $loanCountData = [];
        $savingsData = [];

        $steps = 12;
        $interval = 'month';

        if ($this->type === 'daily') {
            $steps = 14;
            $interval = 'day';
        } elseif ($this->type === 'weekly') {
            $steps = 8;
            $interval = 'week';
        } elseif ($this->type === 'monthly') {
            $steps = 12;
            $interval = 'month';
        } elseif ($this->type === 'yearly') {
            $steps = 5;
            $interval = 'year';
        }

        for ($i = $steps - 1; $i >= 0; $i--) {
            $currentStart = null;
            $currentEnd = null;
            $label = '';

            if ($interval === 'day') {
                $date = \App\Models\Organization::systemNow()->subDays($i);
                $currentStart = $date->copy()->startOfDay();
                $currentEnd = $date->copy()->endOfDay();
                $label = $date->format('D, d M');
            } elseif ($interval === 'week') {
                $date = \App\Models\Organization::systemNow()->subWeeks($i);
                $currentStart = $date->copy()->startOfWeek();
                $currentEnd = $date->copy()->endOfWeek();
                $label = 'Wk '.$date->format('W');
            } elseif ($interval === 'month') {
                $date = \App\Models\Organization::systemNow()->subMonths($i);
                $currentStart = $date->copy()->startOfMonth();
                $currentEnd = $date->copy()->endOfMonth();
                $label = $date->format('M Y');
            } elseif ($interval === 'year') {
                $year = \App\Models\Organization::systemNow()->subYears($i)->year;
                $currentStart = \Carbon\Carbon::create($year, 1, 1)->startOfDay();
                $currentEnd = \Carbon\Carbon::create($year, 12, 31)->endOfDay();
                $label = (string) $year;
            }

            $labels[] = $label;

            $disbursedData[] = Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->where(function ($q) use ($currentStart, $currentEnd) {
                    $q->whereBetween('release_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                        ->orWhere(function ($sq) use ($currentStart, $currentEnd) {
                            $sq->whereNull('release_date')
                                ->whereBetween('created_at', [$currentStart, $currentEnd]);
                        });
                })
                ->sum('amount');

            $collectedData[] = Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->whereBetween('paid_at', [$currentStart, $currentEnd])
                ->sum('amount');

            $interestExpectedData[] = Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->where(function ($q) use ($currentStart, $currentEnd) {
                    $q->whereBetween('release_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                        ->orWhere(function ($sq) use ($currentStart, $currentEnd) {
                            $sq->whereNull('release_date')
                                ->whereBetween('created_at', [$currentStart, $currentEnd]);
                        });
                })
                ->get()
                ->reduce(function ($carry, $loan) {
                    return $carry + $loan->getTotalExpectedInterest();
                }, 0.0);

            $interestPaidData[] = Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->whereBetween('paid_at', [$currentStart, $currentEnd])
                ->sum('interest_amount');

            $customerData[] = Borrower::where('organization_id', $orgId)
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->count();

            $loanCountData[] = Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->where(function ($q) use ($currentStart, $currentEnd) {
                    $q->whereBetween('release_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                        ->orWhere(function ($sq) use ($currentStart, $currentEnd) {
                            $sq->whereNull('release_date')
                                ->whereBetween('created_at', [$currentStart, $currentEnd]);
                        });
                })
                ->count();

            $dep = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->where('type', 'deposit')
                ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                ->sum('amount');

            $wit = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->where('type', 'withdrawal')
                ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                ->sum('amount');

            $savingsData[] = (float) ($dep - $wit);
        }

        return [
            'labels' => $labels,
            'disbursed' => $disbursedData,
            'collected' => $collectedData,
            'interestExpected' => $interestExpectedData,
            'interestPaid' => $interestPaidData,
            'customers' => $customerData,
            'loans' => $loanCountData,
            'savings' => $savingsData,
        ];
    }

    public function render()
    {
        return view('livewire.general-report-print')->layout('layouts.print');
    }
}

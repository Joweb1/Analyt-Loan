<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Reports extends Component
{
    public $reportType = 'daily'; // daily, weekly, monthly, yearly, custom

    public $customStartDate;

    public $customEndDate;

    public function setReportType($type)
    {
        $this->reportType = $type;
        $orgId = Auth::user()->organization_id;
        self::clearCache($orgId);
        $this->render(true);
    }

    public function setCustomDates($start, $end)
    {
        $this->customStartDate = $start;
        $this->customEndDate = $end;
        $this->reportType = 'custom';
        $orgId = Auth::user()->organization_id;
        self::clearCache($orgId);
        $this->render(true);
    }

    public function exportLoans()
    {
        $orgId = Auth::user()->organization_id;
        $loans = Loan::where('organization_id', $orgId)->with('borrower.user')->latest()->get();
        $filename = 'loans_export_'.\App\Models\Organization::systemNow()->format('Y-m-d').'.csv';

        $callback = function () use ($loans) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Loan ID', 'Borrower', 'Amount', 'Product', 'Status', 'Release Date', 'Interest Rate']);
            foreach ($loans as $loan) {
                fputcsv($file, [
                    $loan->loan_number,
                    $loan->borrower->user->name,
                    $loan->amount,
                    $loan->loan_product,
                    $loan->status,
                    $loan->release_date?->format('Y-m-d'),
                    $loan->interest_rate.'%',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    public function exportCustomers()
    {
        $orgId = Auth::user()->organization_id;
        $borrowers = \App\Models\Borrower::where('organization_id', $orgId)->with('user')->latest()->get();
        $filename = 'customers_export_'.\App\Models\Organization::systemNow()->format('Y-m-d').'.csv';

        $callback = function () use ($borrowers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'BVN', 'NIN', 'Credit Score', 'Repayment Score', 'Gender']);
            foreach ($borrowers as $b) {
                fputcsv($file, [
                    $b->user->name,
                    $b->user->email,
                    $b->phone,
                    $b->bvn,
                    $b->national_identity_number,
                    $b->credit_score,
                    $b->trust_score.'%',
                    $b->gender,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    public function exportCollateral()
    {
        $orgId = Auth::user()->organization_id;
        $assets = \App\Models\Collateral::where('organization_id', $orgId)->with('loan.borrower.user')->latest()->get();
        $filename = 'collateral_export_'.\App\Models\Organization::systemNow()->format('Y-m-d').'.csv';

        $callback = function () use ($assets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Asset Name', 'Type', 'Value', 'Condition', 'Status', 'Owner/Loan']);
            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->name,
                    $asset->type,
                    $asset->value,
                    $asset->condition,
                    $asset->status,
                    $asset->loan ? ($asset->loan->borrower->user->name.' (#'.$asset->loan->loan_number.')') : 'Company Asset',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    public function exportStaff()
    {
        $orgId = Auth::user()->organization_id;
        $staff = \App\Models\User::where('organization_id', $orgId)
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('name', ['Borrower']);
            })
            ->get();
        $filename = 'staff_export_'.\App\Models\Organization::systemNow()->format('Y-m-d').'.csv';

        $callback = function () use ($staff) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'Role']);
            foreach ($staff as $s) {
                fputcsv($file, [
                    $s->name,
                    $s->email,
                    $s->phone,
                    $s->getRoleNames()->first(),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    public static function clearCache(string $orgId): void
    {
        $types = ['daily', 'weekly', 'monthly', 'yearly', 'custom'];
        foreach ($types as $t) {
            \Illuminate\Support\Facades\Cache::forget("reports_stats_{$orgId}_{$t}");
        }
    }

    public function getListeners()
    {
        $orgId = Auth::user()->organization_id;

        return [
            "echo:organization.{$orgId},.dashboard.updated" => 'refreshReportsAndForce',
            'echo:dashboard,.dashboard.updated' => 'refreshReportsAndForce',
        ];
    }

    public function refreshReportsAndForce()
    {
        $this->render(true);
    }

    public function render($force = false)
    {
        $orgId = Auth::user()->organization_id;

        $suffix = ($this->reportType === 'custom' && $this->customStartDate && $this->customEndDate)
            ? '_'.md5($this->customStartDate.$this->customEndDate)
            : '';
        $cacheKey = "reports_stats_{$orgId}_{$this->reportType}{$suffix}";

        if ($force) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHour(), function () use ($orgId) {
            $startDate = \App\Models\Organization::systemNow();
            $endDate = \App\Models\Organization::systemNow();

            if ($this->reportType === 'daily') {
                $startDate = \App\Models\Organization::systemNow()->startOfDay();
                $endDate = \App\Models\Organization::systemNow()->endOfDay();
            } elseif ($this->reportType === 'weekly') {
                $startDate = \App\Models\Organization::systemNow()->startOfWeek();
                $endDate = \App\Models\Organization::systemNow()->endOfWeek();
            } elseif ($this->reportType === 'monthly') {
                $startDate = \App\Models\Organization::systemNow()->startOfMonth();
                $endDate = \App\Models\Organization::systemNow()->endOfMonth();
            } elseif ($this->reportType === 'yearly') {
                $startDate = \App\Models\Organization::systemNow()->startOfYear();
                $endDate = \App\Models\Organization::systemNow()->endOfYear();
            } elseif ($this->reportType === 'custom' && $this->customStartDate && $this->customEndDate) {
                $startDate = \Carbon\Carbon::parse($this->customStartDate)->startOfDay();
                $endDate = \Carbon\Carbon::parse($this->customEndDate)->endOfDay();
            }

            // 1. Total Disbursed (Period)
            $disbursed = (float) Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('release_date', [$startDate->startOfDay()->toDateTimeString(), $endDate->endOfDay()->toDateTimeString()])
                        ->orWhere(function ($sq) use ($startDate, $endDate) {
                            $sq->whereNull('release_date')
                                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
                        });
                })
                ->sum('amount') / 100;

            // 2. Total Loans Count (Period)
            $totalLoansCount = Loan::where('organization_id', $orgId)
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
            $collected = (float) Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('amount') / 100;

            // 4. New Customers in Period
            $newCustomers = \App\Models\Borrower::where('organization_id', $orgId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // 5. Net Savings Growth in Period
            $savingsDeposits = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->where('type', 'deposit')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount') / 100;

            $savingsWithdrawals = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->where('type', 'withdrawal')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->sum('amount') / 100;

            $totalSavingsPeriod = (float) ($savingsDeposits - $savingsWithdrawals);

            // 6. Total Expected Interest (LIFETIME)
            $totalExpectedInterestLifetime = (float) Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->get()
                ->reduce(function ($carry, $loan) {
                    return $carry + $loan->getTotalExpectedInterest()->getMajorAmount();
                }, 0.0);

            // 7. Total Paid Interest (LIFETIME) - for the Rem calculation
            $totalPaidInterestLifetime = (float) Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })->sum('interest_amount') / 100;

            $remainingInterestLifetime = max(0, $totalExpectedInterestLifetime - $totalPaidInterestLifetime);

            // 8. Portfolio at Risk (Loans that became overdue in period)
            $overdueLoanIds = ScheduledRepayment::where('status', 'overdue')
                ->whereHas('loan', function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                })
                ->whereBetween('due_date', [$startDate, $endDate])
                ->pluck('loan_id')
                ->unique();

            $totalPAR = Loan::whereIn('id', $overdueLoanIds)->get()->sum(function ($loan) {
                $totalPaidPrincipal = $loan->repayments()->sum('principal_amount') / 100;

                return max(0, $loan->amount->getMajorAmount() - (float) $totalPaidPrincipal);
            });

            // 9. Profit & Loss (PnL) in Period
            $periodPaidInterest = (float) Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->sum('interest_amount') / 100;

            $totalFeesPeriod = (float) Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('release_date', [$startDate->startOfDay()->toDateTimeString(), $endDate->endOfDay()->toDateTimeString()])
                        ->orWhere(function ($sq) use ($startDate, $endDate) {
                            $sq->whereNull('release_date')
                                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
                        });
                })
                ->sum(\Illuminate\Support\Facades\DB::raw('processing_fee + insurance_fee')) / 100;

            $totalPnLPeriod = $periodPaidInterest + $totalFeesPeriod;

            // 10. Organization Balance (Snapshot of Outstanding Principal + Expected Interest)
            // This is the true "Balance" of what is out in the field.
            $orgBalance = Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'overdue'])
                ->get()
                ->sum(function ($loan) {
                    return $loan->balance->getMajorAmount(); // using getBalanceAttribute
                });

            // Chart Data (History based on type, independent of period filters)
            $chartData = $this->getChartData($orgId);

            return [
                'disbursed' => $disbursed,
                'totalLoansCount' => $totalLoansCount,
                'collected' => $collected,
                'newCustomers' => $newCustomers,
                'totalSavings' => $totalSavingsPeriod,
                'totalInterest' => $totalExpectedInterestLifetime,
                'totalPaidInterest' => $totalPaidInterestLifetime,
                'remainingInterest' => $remainingInterestLifetime,
                'totalPAR' => $totalPAR,
                'totalPnL' => $totalPnLPeriod,
                'orgBalance' => $orgBalance,
                'chartData' => $chartData,
            ];
        });

        $this->dispatch('chartUpdated', chartData: $data['chartData']);

        return view('livewire.reports', $data)->layout('layouts.app', ['title' => 'Organization Reports']);
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

        // Filter affects granularity but not specific range
        if ($this->reportType === 'daily') {
            $steps = 14;
            $interval = 'day';
        } elseif ($this->reportType === 'weekly') {
            $steps = 8;
            $interval = 'week';
        } elseif ($this->reportType === 'monthly' || $this->reportType === 'custom') {
            $steps = 12;
            $interval = 'month';
        } elseif ($this->reportType === 'yearly') {
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

            // Trends (Historical)
            $disbursedData[] = Loan::where('organization_id', $orgId)
                ->whereIn('status', ['approved', 'active', 'repaid', 'overdue'])
                ->where(function ($q) use ($currentStart, $currentEnd) {
                    $q->whereBetween('release_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                        ->orWhere(function ($sq) use ($currentStart, $currentEnd) {
                            $sq->whereNull('release_date')
                                ->whereBetween('created_at', [$currentStart, $currentEnd]);
                        });
                })
                ->sum('amount') / 100;

            $collectedData[] = Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->whereBetween('paid_at', [$currentStart, $currentEnd])
                ->sum('amount') / 100;

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
                    return $carry + $loan->getTotalExpectedInterest()->getMajorAmount();
                }, 0.0);

            $interestPaidData[] = Repayment::whereHas('loan', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->whereBetween('paid_at', [$currentStart, $currentEnd])
                ->sum('interest_amount') / 100;

            $customerData[] = \App\Models\Borrower::where('organization_id', $orgId)
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
                ->sum('amount') / 100;

            $wit = \App\Models\SavingsTransaction::whereHas('savingsAccount', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
                ->where('type', 'withdrawal')
                ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
                ->sum('amount') / 100;

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
}

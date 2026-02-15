<?php

namespace App\Livewire;

use App\Models\Loan;
use App\Models\Repayment;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class Reports extends Component
{
    public $reportType = 'daily'; // daily, weekly, monthly

    public function setReportType($type)
    {
        $this->reportType = $type;
        $orgId = Auth::user()->organization_id;
        $this->dispatch('chartUpdated', chartData: $this->getChartData($orgId));
    }

    public function exportLoans()
    {
        $orgId = Auth::user()->organization_id;
        $loans = Loan::where('organization_id', $orgId)->with('borrower.user')->latest()->get();
        $filename = "loans_export_" . now()->format('Y-m-d') . ".csv";
        
        $callback = function() use ($loans) {
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
                    $loan->interest_rate . '%'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ]);
    }

    public function exportCustomers()
    {
        $orgId = Auth::user()->organization_id;
        $borrowers = \App\Models\Borrower::where('organization_id', $orgId)->with('user')->latest()->get();
        $filename = "customers_export_" . now()->format('Y-m-d') . ".csv";

        $callback = function() use ($borrowers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'BVN', 'NIN', 'Credit Score', 'Gender']);
            foreach ($borrowers as $b) {
                fputcsv($file, [
                    $b->user->name,
                    $b->user->email,
                    $b->phone,
                    $b->bvn,
                    $b->national_identity_number,
                    $b->credit_score,
                    $b->gender
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ]);
    }

    public function exportCollateral()
    {
        $orgId = Auth::user()->organization_id;
        $assets = \App\Models\Collateral::where('organization_id', $orgId)->with('loan.borrower.user')->latest()->get();
        $filename = "collateral_export_" . now()->format('Y-m-d') . ".csv";

        $callback = function() use ($assets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Asset Name', 'Type', 'Value', 'Condition', 'Status', 'Owner/Loan']);
            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->name,
                    $asset->type,
                    $asset->value,
                    $asset->condition,
                    $asset->status,
                    $asset->loan ? ($asset->loan->borrower->user->name . ' (#' . $asset->loan->loan_number . ')') : 'Company Asset'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ]);
    }

    public function exportStaff()
    {
        $orgId = Auth::user()->organization_id;
        $staff = \App\Models\User::where('organization_id', $orgId)
            ->whereHas('roles', function($q) { $q->whereNotIn('name', ['Borrower']); })
            ->get();
        $filename = "staff_export_" . now()->format('Y-m-d') . ".csv";

        $callback = function() use ($staff) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'Role']);
            foreach ($staff as $s) {
                fputcsv($file, [
                    $s->name,
                    $s->email,
                    $s->phone,
                    $s->getRoleNames()->first()
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ]);
    }
    
    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $startDate = now();
        $endDate = now();

        if ($this->reportType === 'daily') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($this->reportType === 'weekly') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        $disbursed = Loan::where('organization_id', $orgId)
            ->whereBetween('release_date', [$startDate, $endDate])
            ->sum('amount');

        $collected = Repayment::whereHas('loan', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        $newCustomers = \App\Models\Borrower::where('organization_id', $orgId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Chart Data
        $chartData = $this->getChartData($orgId);

        return view('livewire.reports', [
            'disbursed' => $disbursed,
            'collected' => $collected,
            'newCustomers' => $newCustomers,
            'chartData' => $chartData,
        ])->layout('layouts.app');
    }

    protected function getChartData($orgId)
    {
        $labels = [];
        $disbursedData = [];
        $collectedData = [];

        if ($this->reportType === 'daily') {
            // Last 14 days
            for ($i = 13; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('D, d M');
                
                $disbursedData[] = Loan::where('organization_id', $orgId)
                    ->whereDate('release_date', $date->toDateString())
                    ->sum('amount');

                $collectedData[] = Repayment::whereHas('loan', function($q) use ($orgId) {
                        $q->where('organization_id', $orgId);
                    })
                    ->whereDate('paid_at', $date->toDateString())
                    ->sum('amount');
            }
        } elseif ($this->reportType === 'weekly') {
            // Last 8 weeks
            for ($i = 7; $i >= 0; $i--) {
                $start = now()->subWeeks($i)->startOfWeek();
                $end = now()->subWeeks($i)->endOfWeek();
                $labels[] = 'Week ' . $start->format('W');

                $disbursedData[] = Loan::where('organization_id', $orgId)
                    ->whereBetween('release_date', [$start, $end])
                    ->sum('amount');

                $collectedData[] = Repayment::whereHas('loan', function($q) use ($orgId) {
                        $q->where('organization_id', $orgId);
                    })
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('amount');
            }
        } else {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $labels[] = $month->format('M Y');

                $disbursedData[] = Loan::where('organization_id', $orgId)
                    ->whereMonth('release_date', $month->month)
                    ->whereYear('release_date', $month->year)
                    ->sum('amount');

                $collectedData[] = Repayment::whereHas('loan', function($q) use ($orgId) {
                        $q->where('organization_id', $orgId);
                    })
                    ->whereMonth('paid_at', $month->month)
                    ->whereYear('paid_at', $month->year)
                    ->sum('amount');
            }
        }

        return [
            'labels' => $labels,
            'disbursed' => $disbursedData,
            'collected' => $collectedData,
        ];
    }
}

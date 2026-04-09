<?php

namespace App\Livewire\Admin;

use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Reports extends Component
{
    public $startDate;

    public $endDate;

    public function mount()
    {
        if (! Auth::user()->isAppOwner()) {
            abort(403);
        }

        $this->startDate = \App\Models\Organization::systemNow()->startOfMonth()->format('Y-m-d');
        $this->endDate = \App\Models\Organization::systemNow()->format('Y-m-d');
    }

    public function render()
    {
        $orgStats = Organization::withCount(['loans', 'borrowers', 'staff'])
            ->withSum(['loans as total_lent' => function ($query) {
                $query->whereIn('status', ['active', 'repaid', 'overdue']);
            }], 'amount')
            ->get()
            ->map(function ($org) {
                $org->total_lent = (float) ($org->total_lent ?? 0) / 100;
                $org->total_collected = Repayment::withoutGlobalScopes()
                    ->whereHas('loan', function ($q) use ($org) {
                        $q->where('organization_id', $org->id);
                    })->sum('amount') / 100;

                return $org;
            });

        // Platform Totals
        $totals = [
            'lent' => Loan::withoutGlobalScopes()
                ->whereIn('status', ['active', 'repaid', 'overdue'])
                ->sum('amount') / 100,
            'collected' => Repayment::withoutGlobalScopes()->sum('amount') / 100,
            'organizations' => Organization::count(),
            'borrowers' => \App\Models\Borrower::count(),
        ];

        return view('livewire.admin.reports', [
            'orgStats' => $orgStats,
            'totals' => $totals,
        ])->layout('layouts.app', ['title' => 'Platform Reports']);
    }
}

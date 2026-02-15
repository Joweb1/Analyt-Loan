<?php

namespace App\Livewire\Components;

use App\Models\Loan;
use App\Models\Borrower;
use App\Models\Collateral;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class OmnibarSearch extends Component
{
    public $query = '';
    public $results = [];

    public function updatedQuery()
    {
        if (strlen($this->query) < 2) {
            $this->results = [];
            return;
        }

        $orgId = Auth::user()->organization_id;

        // Search Borrowers
        $borrowers = Borrower::with('user')
            ->where('organization_id', $orgId)
            ->where(function($q) {
                $q->whereHas('user', function($uq) {
                    $uq->where('name', 'like', '%' . $this->query . '%')
                       ->orWhere('email', 'like', '%' . $this->query . '%');
                })
                ->orWhere('phone', 'like', '%' . $this->query . '%')
                ->orWhere('bvn', 'like', '%' . $this->query . '%')
                ->orWhere('national_identity_number', 'like', '%' . $this->query . '%');
            })
            ->take(4)
            ->get()
            ->map(function($b) {
                return [
                    'type' => 'borrower',
                    'title' => $b->user->name,
                    'subtitle' => 'Customer | ' . $b->phone,
                    'link' => route('borrower.loans', $b->id),
                    'icon' => 'person'
                ];
            });

        // Search Loans
        $loans = Loan::where('organization_id', $orgId)
            ->where(function($q) {
                $q->where('loan_number', 'like', '%' . $this->query . '%')
                  ->orWhere('amount', 'like', '%' . $this->query . '%');
            })
            ->take(4)
            ->get()
            ->map(function($l) {
                return [
                    'type' => 'loan',
                    'title' => 'Loan ' . $l->loan_number,
                    'subtitle' => 'Amount: ₦' . number_format($l->amount),
                    'link' => route('loan.show', $l->id),
                    'icon' => 'payments'
                ];
            });

        // Search Collateral
        $collateral = Collateral::where('organization_id', $orgId)
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->query . '%')
                  ->orWhere('description', 'like', '%' . $this->query . '%');
            })
            ->take(4)
            ->get()
            ->map(function($c) {
                return [
                    'type' => 'collateral',
                    'title' => $c->name,
                    'subtitle' => 'Collateral | Value: ₦' . number_format($c->value),
                    'link' => route('vault'), // Ideally scroll to or filter vault
                    'icon' => 'inventory_2'
                ];
            });

        $this->results = $borrowers->merge($loans)->merge($collateral)->toArray();
    }

    public function render()
    {
        return view('livewire.components.omnibar-search');
    }
}

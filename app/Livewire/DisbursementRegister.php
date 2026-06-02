<?php

namespace App\Livewire;

use App\Models\Loan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DisbursementRegister extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $status = '';

    #[Url]
    public $loan_type = '';

    protected $listeners = ['refreshRegister' => '$refresh'];

    public function updating($property)
    {
        if (in_array($property, ['search', 'status', 'loan_type'])) {
            $this->resetPage();
        }
    }

    public function updateInstallmentDate($loanId, $date)
    {
        $loan = Loan::findOrFail($loanId);
        $loan->update(['installment_date' => $date]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Installment date updated for '.$loan->borrower->user->name,
        ]);
    }

    public function updateNote($loanId, $note)
    {
        $loan = Loan::findOrFail($loanId);
        $loan->update(['register_notes' => $note]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Notes updated for '.$loan->borrower->user->name,
        ]);
    }

    public function render()
    {
        $query = Loan::with(['borrower.user', 'borrower.savingsAccount', 'loanOfficer'])
            ->whereNotNull('release_date')
            ->orderBy('release_date', 'desc');

        if ($this->search) {
            $term = '%'.strtolower(trim($this->search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('borrower.user', function ($sq) use ($term) {
                    $sq->whereRaw('LOWER(name) LIKE ?', [$term]);
                })->orWhere('loan_number', 'like', $term);
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->loan_type) {
            $query->where('loan_product', $this->loan_type);
        }

        $allLoans = $query->get();

        // Grouping by Month/Year
        $groupedLoans = $allLoans->groupBy(function ($loan) {
            return $loan->release_date->format('F Y');
        });

        // Summary Calculations
        $stats = [
            'total_issued' => $this->formatMoney($allLoans->sum(fn ($l) => $l->amount->getMinorAmount())),
            'active_count' => $allLoans->where('status', 'active')->count(),
            'completed_count' => $allLoans->where('status', 'completed')->count(),
            'total_repayment_value' => $this->formatMoney($allLoans->sum(fn ($l) => $l->getTotalCost()->getMinorAmount())),
        ];

        return view('livewire.disbursement-register', [
            'groupedLoans' => $groupedLoans,
            'stats' => $stats,
            'loanProducts' => Loan::distinct()->pluck('loan_product'),
        ])->layout('layouts.app', ['title' => 'Disbursement Register']);
    }

    private function formatMoney($amount)
    {
        // Simple format for summary, assuming default currency
        return number_format($amount / 100, 2);
    }
}

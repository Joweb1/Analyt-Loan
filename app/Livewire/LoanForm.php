<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Rules\FiftyPercentRule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class LoanForm extends Component
{
    use WithFileUploads;

    // Borrower Selection
    public $borrowerId;
    public $selectedBorrower;
    public $search = '';
    public $searchResults = [];
    public $showBorrowerModal = false;

    // Loan Details
    public $loan_number;
    public $loan_product;
    public $release_date;
    public $amount;
    public $interest_rate;
    public $interest_type = 'year';
    public $duration = 1;
    public $duration_unit = 'month';
    public $repayment_cycle = 'monthly';
    public $num_repayments = 1;

    // Fees & extras
    public $processing_fee;
    public $processing_fee_type = 'fixed';
    public $insurance_fee;
    public $description;
    public $attachments; // File upload

    // Collateral
    public $collateralId;
    public $collaterals;

    protected $rules = [
        'borrowerId' => 'required|exists:borrowers,id',
        'loan_number' => 'required|unique:loans,loan_number',
        'loan_product' => 'required|string',
        'release_date' => 'required|date',
        'amount' => 'required|numeric|min:1',
        'interest_rate' => 'required|numeric|min:0',
        'interest_type' => 'required|in:year,month,week,day',
        'duration' => 'required|integer|min:1',
        'duration_unit' => 'required|in:year,month,week,day',
        'repayment_cycle' => 'required|in:daily,weekly,biweekly,monthly,yearly',
        'num_repayments' => 'required|integer|min:1',
        'processing_fee' => 'nullable|numeric|min:0',
        'processing_fee_type' => 'nullable|in:fixed,percentage',
        'insurance_fee' => 'nullable|numeric|min:0',
        'description' => 'nullable|string',
        'collateralId' => 'nullable|exists:collaterals,id',
        'attachments' => 'nullable|file|max:10240', // 10MB max
    ];

    public function mount()
    {
        $this->collaterals = Collateral::whereNull('loan_id')->get();
        $this->release_date = now()->format('Y-m-d');
    }

    // Search Logic
    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Borrower::with('user')
            ->where(function ($query) {
                $query->where('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('bvn', 'like', '%' . $this->search . '%')
                      ->orWhere('national_identity_number', 'like', '%' . $this->search . '%');
            })
            ->orWhereHas('user', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->take(10)
            ->get();
    }

    public function selectBorrower($id)
    {
        $this->borrowerId = $id;
        $this->selectedBorrower = Borrower::with('user')->find($id);
        $this->generateLoanNumber();
        $this->showBorrowerModal = false;
        $this->search = '';
        $this->searchResults = [];
    }

    public function resetBorrower()
    {
        $this->borrowerId = null;
        $this->selectedBorrower = null;
        $this->search = '';
        $this->searchResults = [];
        // Optionally reset loan number if it depends on borrower, but usually tracking IDs persist or regenerate.
        // Let's regenerate or clear it.
        $this->loan_number = null; 
    }

    public function generateLoanNumber()
    {
        // Example: LN-2026-X8Y9Z
        $this->loan_number = 'LN-' . date('Y') . '-' . strtoupper(Str::random(5));
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'amount' || $propertyName === 'collateralId') {
            $rules = [
                'amount' => 'required|numeric|min:1',
                'collateralId' => ['nullable', 'exists:collaterals,id'],
            ];

            if ($this->collateralId) {
                $rules['collateralId'][] = new FiftyPercentRule((float)($this->amount ?? 0));
            }

            $this->validateOnly($propertyName, $rules);
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function saveLoan()
    {
        $rules = [
            'borrowerId' => 'required|exists:borrowers,id',
            'loan_number' => 'required|unique:loans,loan_number',
            'loan_product' => 'required|string',
            'release_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'interest_rate' => 'required|numeric|min:0',
            'interest_type' => 'required|in:year,month,week,day',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:year,month,week,day',
            'repayment_cycle' => 'required|in:daily,weekly,biweekly,monthly,yearly',
            'num_repayments' => 'required|integer|min:1',
            'processing_fee' => 'nullable|numeric|min:0',
            'processing_fee_type' => 'nullable|in:fixed,percentage',
            'insurance_fee' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'collateralId' => ['nullable', 'exists:collaterals,id'],
            'attachments' => 'nullable|file|max:10240',
        ];

        if ($this->collateralId) {
            $rules['collateralId'][] = new FiftyPercentRule((float)($this->amount ?? 0));
        }

        try {
            $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('custom-alert', ['message' => 'Validation failed. Please check the form.', 'type' => 'error']);
            throw $e;
        }

        $attachmentPath = null;
        if ($this->attachments) {
            $attachmentPath = $this->attachments->store('loan-attachments', 'public');
        }

        $loan = Loan::create([
            'borrower_id' => $this->borrowerId,
            'loan_number' => $this->loan_number,
            'loan_product' => $this->loan_product,
            'release_date' => $this->release_date,
            'amount' => $this->amount,
            'interest_rate' => $this->interest_rate,
            'interest_type' => $this->interest_type,
            'duration' => $this->duration,
            'duration_unit' => $this->duration_unit,
            'repayment_cycle' => $this->repayment_cycle,
            'num_repayments' => $this->num_repayments,
            'processing_fee' => $this->processing_fee,
            'processing_fee_type' => $this->processing_fee_type,
            'insurance_fee' => $this->insurance_fee,
            'description' => $this->description,
            'attachments' => $attachmentPath ? [$attachmentPath] : null,
        ]);

        // Link Collateral
        $collateral = Collateral::find($this->collateralId);
        if ($collateral) {
            $collateral->loan_id = $loan->id;
            $collateral->status = 'in_vault';
            $collateral->save();
        }

        $this->dispatch('custom-alert', ['message' => 'Loan created successfully with Number: ' . $this->loan_number, 'type' => 'success']);

        $this->reset([
            'borrowerId', 'selectedBorrower', 'loan_number', 'loan_product', 'amount', 
            'interest_rate', 'processing_fee', 'insurance_fee', 'description', 
            'attachments', 'collateralId'
        ]);
        
        $this->mount(); // Refresh collaterals
    }

    public function render()
    {
        return view('livewire.components.loan-form');
    }
}

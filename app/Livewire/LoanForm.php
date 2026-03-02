<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Rules\FiftyPercentRule;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class LoanForm extends Component
{
    use WithFileUploads;

    /** @var \App\Models\Borrower|null */
    public $selectedBorrower;

    // Borrower Selection
    public $borrowerId;

    public $borrowerUserId;

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

    public $loan_officer_id;

    public $guarantor_id;

    public $guarantor_type;

    #[On('guarantorSelected')]
    public function updateGuarantor($guarantor)
    {
        if ($guarantor) {
            $this->guarantor_id = $guarantor['id'];
            $this->guarantor_type = $guarantor['type'];
        } else {
            $this->guarantor_id = null;
            $this->guarantor_type = null;
        }
    }

    // Collateral
    public $collateralId;

    public $collaterals;

    public $staffMembers;

    public $loanProducts = [];

    // Edit Mode State
    public $loanId;

    public $isEditMode = false;

    protected function rules()
    {
        return [
            'borrowerId' => 'required|exists:borrowers,id',
            'loan_officer_id' => 'nullable|exists:users,id',
            'loan_number' => 'required|unique:loans,loan_number'.($this->isEditMode ? ','.$this->loanId : ''),
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
            'attachments' => ['nullable', 'file', 'max:10240'], // 10MB max
            'guarantor_id' => 'nullable|string',
            'guarantor_type' => 'nullable|in:internal,external',
        ];
    }

    public function mount(?Loan $loan = null)
    {
        $orgId = Auth::user()->organization_id;
        $this->loanProducts = \App\Models\LoanProduct::orderBy('name')->get();

        if ($loan && $loan->exists) {
            $this->isEditMode = true;
            $this->loanId = $loan->id;
            $this->borrowerId = $loan->borrower_id;
            /** @var \App\Models\Borrower|null $borrower */
            $borrower = $loan->borrower()->with('user')->first();
            $this->selectedBorrower = $borrower;
            $this->borrowerUserId = $borrower?->user_id;
            $this->loan_number = $loan->loan_number;
            $this->loan_product = $loan->loan_product;
            $this->release_date = $loan->release_date ? $loan->release_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->amount = $loan->amount;
            $this->interest_rate = $loan->interest_rate;
            $this->interest_type = $loan->interest_type;
            $this->duration = $loan->duration;
            $this->duration_unit = $loan->duration_unit;
            $this->repayment_cycle = $loan->repayment_cycle;
            $this->num_repayments = $loan->num_repayments;
            $this->processing_fee = $loan->processing_fee;
            $this->processing_fee_type = $loan->processing_fee_type;
            $this->insurance_fee = $loan->insurance_fee;
            $this->description = $loan->description;
            $this->collateralId = $loan->collateral?->id;
            $this->loan_officer_id = $loan->loan_officer_id;

            if ($loan->external_guarantor_id) {
                $this->guarantor_id = $loan->external_guarantor_id;
                $this->guarantor_type = 'external';
            } elseif ($loan->guarantor_id) {
                $this->guarantor_id = $loan->guarantor_id;
                $this->guarantor_type = 'internal';
            }
        } else {
            $this->release_date = now()->format('Y-m-d');

            // Check for borrower_id in query string
            if ($borrowerId = request()->query('borrower_id')) {
                $this->selectBorrower($borrowerId);
            }
        }

        $this->collaterals = Collateral::whereNull('loan_id')
            ->when($this->loanId, function ($query) {
                return $query->orWhere('loan_id', $this->loanId);
            })
            ->get();

        $orgId = Auth::user()->organization_id;
        $this->staffMembers = \App\Models\User::where('organization_id', $orgId)
            ->whereHas('roles', function ($q) {
                $q->whereNotIn('name', ['Borrower']);
            })
            ->get();
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
                $query->where('phone', 'like', '%'.$this->search.'%')
                    ->orWhere('bvn', 'like', '%'.$this->search.'%')
                    ->orWhere('national_identity_number', 'like', '%'.$this->search.'%');
            })
            ->orWhereHas('user', function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->take(10)
            ->get();
    }

    public function selectBorrower($id)
    {
        $this->borrowerId = $id;
        /** @var \App\Models\Borrower|null $borrower */
        $borrower = Borrower::with('user')->find($id);
        $this->selectedBorrower = $borrower;
        $this->borrowerUserId = $borrower?->user_id;
        if (! $this->isEditMode) {
            $this->generateLoanNumber();
        }
        $this->showBorrowerModal = false;
        $this->search = '';
        $this->searchResults = [];
    }

    public function resetBorrower()
    {
        $this->borrowerId = null;
        $this->borrowerUserId = null;
        $this->selectedBorrower = null;
        $this->search = '';
        $this->searchResults = [];
        if (! $this->isEditMode) {
            $this->loan_number = null;
        }
    }

    public function generateLoanNumber()
    {
        // Example: LN-2026-X8Y9Z
        $this->loan_number = 'LN-'.date('Y').'-'.strtoupper(Str::random(5));
    }

    public function updatedLoanProduct($value)
    {
        $product = \App\Models\LoanProduct::where('name', $value)->first();
        if ($product) {
            $this->interest_rate = $product->default_interest_rate;
            $this->duration = $product->default_duration;
            $this->duration_unit = $product->duration_unit;
            $this->repayment_cycle = $product->repayment_cycle;
        }
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'amount' || $propertyName === 'collateralId') {
            $rules = [
                'amount' => 'required|numeric|min:1',
                'collateralId' => ['nullable', 'exists:collaterals,id'],
            ];

            if ($this->collateralId) {
                $rules['collateralId'][] = new FiftyPercentRule((float) ($this->amount ?? 0));
            }

            $this->validateOnly($propertyName, $rules);
        } else {
            $this->validateOnly($propertyName, $this->rules());
        }
    }

    public function saveLoan(LoanService $loanService)
    {
        $rules = $this->rules();

        if ($this->collateralId) {
            $rules['collateralId'][] = new FiftyPercentRule((float) ($this->amount ?? 0));
        }

        try {
            $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('custom-alert', ['message' => 'Validation failed. Please check the form.', 'type' => 'error']);
            throw $e;
        }

        $data = [
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
            'loan_officer_id' => $this->loan_officer_id,
            'guarantor_id' => $this->guarantor_type === 'internal' ? $this->guarantor_id : null,
            'external_guarantor_id' => $this->guarantor_type === 'external' ? $this->guarantor_id : null,
        ];

        if ($this->isEditMode) {
            $loan = Loan::find($this->loanId);
            $loanService->updateLoan($loan, $data, $this->attachments, $this->collateralId);
            $message = 'Loan details updated successfully.';
        } else {
            $loan = $loanService->createLoan($data, $this->attachments, $this->collateralId);
            $message = 'Loan created successfully with Number: '.$this->loan_number;
        }

        $this->dispatch('custom-alert', ['message' => $message, 'type' => 'success']);

        if (! $this->isEditMode) {
            $this->reset([
                'borrowerId', 'selectedBorrower', 'loan_number', 'loan_product', 'amount',
                'interest_rate', 'processing_fee', 'insurance_fee', 'description',
                'attachments', 'collateralId',
            ]);
            $this->mount();
        } else {
            return redirect()->route('loan.show', $this->loanId);
        }
    }

    public function render()
    {
        return view('livewire.components.loan-form');
    }
}

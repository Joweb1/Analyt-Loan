<?php

namespace App\Livewire\Borrower;

use App\Models\Loan;
use App\Models\LoanProduct;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Borrow extends Component
{
    public $creditLimit;

    public $amount;

    public $selectedProduct;

    public $loanProducts = [];

    // Loan Terms (Derived from Product)
    public $interest_rate;

    public $interest_type = 'month';

    public $duration;

    public $duration_unit;

    public $repayment_cycle;

    public $num_repayments = 1;

    public $processing_fee = 0;

    public $processing_fee_type = 'fixed';

    public $showBreakdown = false;

    public $showSuccess = false;

    public function mount()
    {
        $user = Auth::user();
        if (! $user->borrower || $user->borrower->kyc_status !== 'approved') {
            return redirect()->route('borrower.home');
        }

        // Check for active loan
        $hasActiveLoan = Loan::where('borrower_id', $user->borrower->id)
            ->whereIn('status', ['active', 'overdue', 'applied', 'approved', 'applied', 'declined']) // Adding declined to block if needed? No, user can re-apply if declined.
            ->where('created_at', '>', \App\Models\Organization::systemNow()->subHours(24)) // Block for 24h if declined? Or just check active.
            ->whereIn('status', ['active', 'overdue', 'applied', 'approved', 'applied'])
            ->exists();

        if ($hasActiveLoan) {
            session()->flash('custom-alert', ['type' => 'warning', 'message' => 'You already have an active or pending loan application.']);

            return redirect()->route('borrower.home');
        }

        // Fetch Organization's Loan Products
        $this->loanProducts = LoanProduct::where('organization_id', $user->organization_id)->get();

        if ($this->loanProducts->isEmpty()) {
            session()->flash('custom-alert', ['type' => 'error', 'message' => 'No loan products available. Please contact support.']);

            return redirect()->route('borrower.home');
        }

        // Trust Score logic for limit
        $score = $user->borrower->trust_score ?? 0;
        $this->creditLimit = min(500000, 50000 + ($score * 2000));

        // Select first product by default
        $this->selectProduct($this->loanProducts->first()->id);

        $this->amount = min(10000, $this->creditLimit);
    }

    public function selectProduct($productId)
    {
        $product = LoanProduct::find($productId);
        if ($product) {
            $this->selectedProduct = $product;
            $this->interest_rate = $product->default_interest_rate;
            $this->interest_type = 'month';
            $this->duration = $product->default_duration;
            $this->duration_unit = $product->duration_unit;
            $this->repayment_cycle = $product->repayment_cycle;

            $this->calculateInstallments();
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['duration', 'duration_unit', 'repayment_cycle'])) {
            $this->calculateInstallments();
        }
    }

    private function calculateInstallments()
    {
        if (! $this->duration || ! $this->duration_unit || ! $this->repayment_cycle) {
            $this->num_repayments = 1;

            return;
        }

        // Convert duration to days for easy calculation
        $totalDays = match ($this->duration_unit) {
            'day' => $this->duration,
            'week' => $this->duration * 7,
            'month' => $this->duration * 30,
            'year' => $this->duration * 365,
            default => $this->duration * 30,
        };

        $cycleDays = match ($this->repayment_cycle) {
            'daily' => 1,
            'weekly' => 7,
            'biweekly' => 14,
            'monthly' => 30,
            'yearly' => 365,
            default => 30,
        };

        $this->num_repayments = max(1, floor($totalDays / $cycleDays));
    }

    public function getCalculatedProperty()
    {
        // Simple flat interest calculation
        $totalInterest = $this->amount * ($this->interest_rate / 100);
        $totalPayable = $this->amount + $totalInterest + $this->processing_fee;

        $installmentAmount = $totalPayable / $this->num_repayments;

        $schedule = [];
        $startDate = \App\Models\Organization::systemNow();
        for ($i = 1; $i <= $this->num_repayments; $i++) {
            $dueDate = $startDate->copy();
            match ($this->repayment_cycle) {
                'daily' => $dueDate->addDays($i),
                'weekly' => $dueDate->addWeeks($i),
                'biweekly' => $dueDate->addWeeks($i * 2),
                'monthly' => $dueDate->addMonths($i),
                'yearly' => $dueDate->addYears($i),
                default => $dueDate->addMonths($i),
            };

            $schedule[] = [
                'installment' => $i,
                'due_date' => $dueDate->format('M d, Y'),
                'amount' => $installmentAmount,
            ];
        }

        return [
            'principal' => $this->amount,
            'interest' => $totalInterest,
            'fees' => $this->processing_fee,
            'total' => $totalPayable,
            'num_installments' => $this->num_repayments,
            'installment_amount' => $installmentAmount,
            'schedule' => $schedule,
        ];
    }

    public function openBreakdown()
    {
        $this->showBreakdown = true;
    }

    public function submitApplication(LoanService $loanService)
    {
        $user = Auth::user();

        $year = \App\Models\Organization::systemNow()->year;
        $data = [
            'borrower_id' => $user->borrower->id,
            'loan_product' => $this->selectedProduct->name,
            'amount' => (float) $this->amount,
            'interest_rate' => (float) $this->interest_rate,
            'interest_type' => $this->interest_type,
            'duration' => (int) $this->duration,
            'duration_unit' => $this->duration_unit,
            'repayment_cycle' => $this->repayment_cycle,
            'num_repayments' => (int) $this->num_repayments,
            'description' => 'Applied via Borrower App',
            'loan_number' => 'LN-'.$year.'-'.strtoupper(Str::random(5)),
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($data);
        $loanService->createLoan($dto);

        $this->showBreakdown = false;
        $this->showSuccess = true;
    }

    public function render()
    {
        return view('livewire.borrower.borrow')->layout('layouts.borrower', ['title' => 'Apply for Loan']);
    }
}

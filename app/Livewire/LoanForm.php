<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Rules\FiftyPercentRule;
use Livewire\Component;

class LoanForm extends Component
{
    public $borrowerId;
    public $amount;
    public $collateralId;

    public $borrowers;
    public $collaterals;

    protected $rules = [
        'borrowerId' => 'required|exists:borrowers,id',
        'amount' => 'required|numeric|min:1',
        'collateralId' => ['required', 'exists:collaterals,id'],
    ];

    public function mount()
    {
        $this->borrowers = Borrower::all();
        $this->collaterals = Collateral::whereNull('loan_id')->get();
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'amount' || $propertyName === 'collateralId') {
            $this->validateOnly($propertyName, [
                'collateralId' => ['required', new FiftyPercentRule($this->amount)],
            ]);
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function saveLoan()
    {
        $this->validate([
            'borrowerId' => 'required|exists:borrowers,id',
            'amount' => 'required|numeric|min:1',
            'collateralId' => ['required', 'exists:collaterals,id', new FiftyPercentRule($this->amount)],
        ]);

        $loan = Loan::create([
            'borrower_id' => $this->borrowerId,
            'amount' => $this->amount,
        ]);

        $collateral = Collateral::find($this->collateralId);
        $collateral->loan_id = $loan->id;
        $collateral->status = 'in_vault';
        $collateral->save();

        session()->flash('message', 'Loan created successfully.');

        $this->reset(['borrowerId', 'amount', 'collateralId']);
    }

    public function render()
    {
        return view('livewire.loan-form');
    }
}

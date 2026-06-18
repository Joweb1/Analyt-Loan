<?php

namespace App\Livewire\Settings;

use App\Models\LoanProduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LoanProducts extends Component
{
    use WithPagination;

    public $showModal = false;

    public $productId;

    public $name;

    public $description;

    public $default_interest_rate;

    public $interest_calculation_type = 'percentage';

    public $interest_cycle = 'month';

    public $default_duration;

    public $duration_unit = 'month';

    public $repayment_cycle = 'monthly';

    public $processing_fee;

    public $processing_fee_type = 'fixed';

    public $insurance_fee;

    public $insurance_fee_type = 'fixed';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'default_interest_rate' => 'nullable|numeric|min:0',
        'interest_calculation_type' => 'required|in:fixed,percentage',
        'interest_cycle' => 'required|in:day,week,biweekly,month,year',
        'default_duration' => 'nullable|integer|min:1',
        'duration_unit' => 'required|in:year,month,week,day',
        'repayment_cycle' => 'required|in:daily,weekly,biweekly,monthly,yearly',
        'processing_fee' => 'nullable|numeric|min:0',
        'processing_fee_type' => 'required|in:fixed,percentage',
        'insurance_fee' => 'nullable|numeric|min:0',
        'insurance_fee_type' => 'required|in:fixed,percentage',
    ];

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'default_interest_rate' => $this->default_interest_rate,
            'interest_calculation_type' => $this->interest_calculation_type,
            'interest_cycle' => $this->interest_cycle,
            'default_duration' => $this->default_duration,
            'duration_unit' => $this->duration_unit,
            'repayment_cycle' => $this->repayment_cycle,
            'processing_fee' => $this->processing_fee,
            'processing_fee_type' => $this->processing_fee_type,
            'insurance_fee' => $this->insurance_fee,
            'insurance_fee_type' => $this->insurance_fee_type,
        ];

        if ($this->productId) {
            $product = LoanProduct::findOrFail($this->productId);
            $product->update($data);
            $message = 'Loan product updated successfully.';
        } else {
            $data['organization_id'] = Auth::user()->organization_id;
            LoanProduct::create($data);
            $message = 'Loan product added successfully.';
        }

        $this->reset(['showModal', 'productId', 'name', 'description', 'default_interest_rate', 'interest_calculation_type', 'default_duration', 'duration_unit', 'repayment_cycle', 'processing_fee', 'processing_fee_type', 'insurance_fee', 'insurance_fee_type']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => $message]);
    }

    public function edit($id)
    {
        $product = LoanProduct::findOrFail($id);
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->default_interest_rate = $product->default_interest_rate;
        $this->interest_calculation_type = $product->interest_calculation_type;
        $this->interest_cycle = $product->interest_cycle;
        $this->default_duration = $product->default_duration;
        $this->duration_unit = $product->duration_unit;
        $this->repayment_cycle = $product->repayment_cycle;
        $this->processing_fee = $product->processing_fee ? $product->processing_fee->getMajorAmount() : null;
        $this->processing_fee_type = $product->processing_fee_type;
        $this->insurance_fee = $product->insurance_fee ? $product->insurance_fee->getMajorAmount() : null;
        $this->insurance_fee_type = $product->insurance_fee_type;
        $this->showModal = true;
    }

    public function delete($id)
    {
        LoanProduct::findOrFail($id)->delete();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Loan product removed.']);
    }

    public function render()
    {
        return view('livewire.settings.loan-products', [
            'products' => LoanProduct::latest()->paginate(10),
        ])->layout('layouts.app', ['title' => 'Loan Products Settings']);
    }
}

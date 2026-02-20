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

    public $default_duration;

    public $duration_unit = 'month';

    public $repayment_cycle = 'monthly';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'default_interest_rate' => 'nullable|numeric|min:0',
        'default_duration' => 'nullable|integer|min:1',
        'duration_unit' => 'required|in:year,month,week,day',
        'repayment_cycle' => 'required|in:daily,weekly,biweekly,monthly,yearly',
    ];

    public function save()
    {
        $this->validate();

        if ($this->productId) {
            $product = LoanProduct::findOrFail($this->productId);
            $product->update([
                'name' => $this->name,
                'description' => $this->description,
                'default_interest_rate' => $this->default_interest_rate,
                'default_duration' => $this->default_duration,
                'duration_unit' => $this->duration_unit,
                'repayment_cycle' => $this->repayment_cycle,
            ]);
            $message = 'Loan product updated successfully.';
        } else {
            LoanProduct::create([
                'organization_id' => Auth::user()->organization_id,
                'name' => $this->name,
                'description' => $this->description,
                'default_interest_rate' => $this->default_interest_rate,
                'default_duration' => $this->default_duration,
                'duration_unit' => $this->duration_unit,
                'repayment_cycle' => $this->repayment_cycle,
            ]);
            $message = 'Loan product added successfully.';
        }

        $this->reset(['showModal', 'productId', 'name', 'description', 'default_interest_rate', 'default_duration', 'duration_unit', 'repayment_cycle']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => $message]);
    }

    public function edit($id)
    {
        $product = LoanProduct::findOrFail($id);
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->default_interest_rate = $product->default_interest_rate;
        $this->default_duration = $product->default_duration;
        $this->duration_unit = $product->duration_unit;
        $this->repayment_cycle = $product->repayment_cycle;
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

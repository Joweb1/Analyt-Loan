<?php

namespace App\Livewire;

use App\Models\Guarantor;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GuarantorProfile extends Component
{
    public Guarantor $guarantor;

    public $isEditing = false;

    // Form Fields
    public $name;

    public $email;

    public $phone;

    public $address;

    public $employer;

    public $income;

    public function mount(Guarantor $guarantor)
    {
        if (! Auth::user()->hasPermissionTo('manage_borrowers')) {
            abort(403);
        }
        $this->guarantor = $guarantor->load('user');
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->name = $this->guarantor->name;
        $this->email = $this->guarantor->email;
        $this->phone = $this->guarantor->phone;
        $this->address = $this->guarantor->address;
        $this->employer = $this->guarantor->employer;
        $this->income = $this->guarantor->income;
    }

    public function toggleEdit()
    {
        $this->isEditing = ! $this->isEditing;
        if (! $this->isEditing) {
            $this->loadFields();
        }
    }

    public function save()
    {
        if (! Auth::user()->hasPermissionTo('edit_borrowers')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized action.']);

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
        ]);

        $this->guarantor->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'employer' => $this->employer,
            'income' => $this->income,
        ]);

        // If it has a user account, update that too
        if ($this->guarantor->user) {
            $this->guarantor->user->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ]);
        }

        $this->isEditing = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Guarantor profile updated successfully.']);
    }

    public function render()
    {
        // Loans this guarantor is backing (both internal and external links)
        $guaranteedLoans = Loan::where('guarantor_id', $this->guarantor->user_id)
            ->orWhere('external_guarantor_id', $this->guarantor->id)
            ->with(['borrower', 'borrower.user'])
            ->get();

        return view('livewire.guarantor-profile', [
            'guaranteedLoans' => $guaranteedLoans,
        ])->layout('layouts.app', ['title' => 'Guarantor Profile']);
    }
}

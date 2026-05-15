<?php

namespace App\Livewire;

use App\Models\Saver;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SaverProfile extends Component
{
    public Saver $saver;

    public $isEditing = false;

    // Form Fields
    public $name;

    public $email;

    public $phone;

    public $address;

    public $is_daily_saver;

    public $daily_target_amount;

    public $customData = [];

    public function mount(Saver $saver)
    {
        if (! Auth::user()->hasPermissionTo('manage_borrowers')) {
            abort(403);
        }
        $this->saver = $saver->load(['user', 'user.savingsAccount']);
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->name = $this->saver->user->name;
        $this->email = $this->saver->user->email;
        $this->phone = $this->saver->phone;
        $this->address = $this->saver->custom_data['address'] ?? '';
        $this->is_daily_saver = $this->saver->is_daily_saver;
        $this->daily_target_amount = $this->saver->daily_target_amount ? $this->saver->daily_target_amount->getMajorAmount() : 0;
        $this->customData = $this->saver->custom_data ?? [];
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
            'email' => 'nullable|email|unique:users,email,'.$this->saver->user_id,
        ]);

        $this->saver->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->saver->update([
            'is_daily_saver' => $this->is_daily_saver,
            'daily_target_amount' => $this->daily_target_amount,
            'custom_data' => array_merge($this->customData, ['address' => $this->address]),
        ]);

        $this->isEditing = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Saver profile updated successfully.']);
    }

    public function render()
    {
        return view('livewire.saver-profile')->layout('layouts.app', ['title' => 'Saver Profile']);
    }
}

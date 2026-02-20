<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class SecuritySettings extends Component
{
    public $current_password;

    public $password;

    public $password_confirmation;

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Password updated successfully.']);
    }

    public function render()
    {
        return view('livewire.settings.security-settings')->layout('layouts.app', ['title' => 'Security Settings']);
    }
}

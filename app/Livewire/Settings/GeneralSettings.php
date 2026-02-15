<?php

namespace App\Livewire\Settings;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public $name;
    public $rc_number;
    public $email;
    public $phone;
    public $address;
    public $website;
    public $logo;
    public $signature;
    public $kyc_document;
    public $currentLogo;
    public $currentSignature;
    public $currentKyc;

    public $organization;

    // Preferences
    public $interest_rate;
    public $grace_period;
    public $currency = 'NGN';

    public function mount()
    {
        $this->organization = Auth::user()->organization;
        if ($this->organization) {
            $this->name = $this->organization->name;
            $this->rc_number = $this->organization->rc_number;
            $this->email = $this->organization->email;
            $this->phone = $this->organization->phone;
            $this->address = $this->organization->address;
            $this->website = $this->organization->website;
            $this->currentLogo = $this->organization->logo_path;
            $this->currentSignature = $this->organization->signature_path;
            
            $this->interest_rate = $this->organization->default_interest_rate;
            $this->grace_period = $this->organization->grace_period_days;
            $this->currency = $this->organization->currency_code;
        }
    }

    public function save()
    {
        if (!$this->organization) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'No organization associated with this account.']);
            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'rc_number' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
            'signature' => 'nullable|image|max:2048',
            'kyc_document' => 'nullable|file|max:5120',
            'interest_rate' => 'required|numeric|min:0',
            'grace_period' => 'required|integer|min:0',
            'currency' => 'required|string|max:10',
        ]);

        $org = Auth::user()->organization;
        
        $data = [
            'name' => $this->name,
            'rc_number' => $this->rc_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'default_interest_rate' => $this->interest_rate,
            'grace_period_days' => $this->grace_period,
            'currency_code' => $this->currency,
        ];

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        if ($this->signature) {
            $path = $this->signature->store('signatures', 'public');
            $data['signature_path'] = $path;
        }

        if ($this->kyc_document) {
            // Using a generic documents field or adding a new column
            // For now, let's just store it and assume the column exists or I'll add it.
            $path = $this->kyc_document->store('kyc-docs', 'public');
            $data['kyc_document_path'] = $path;
        }

        $org->update($data);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Organization settings updated.']);
    }

    public function render()
    {
        return view('livewire.settings.general-settings')->layout('layouts.app');
    }
}

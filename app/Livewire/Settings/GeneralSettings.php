<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public $organization;

    // Basic Info
    public $name;

    public $tagline; // NEW

    public $rc_number;

    public $email;

    public $phone;

    public $address;

    public $website;

    public $logo;

    public $signature;

    public $currentLogo;

    public $currentSignature;

    public $kyc_document;

    // Branding
    public $brand_color = '#0f172a'; // NEW

    // Repayment Bank Details (NEW)
    public $repayment_bank_name;

    public $repayment_account_number;

    public $repayment_account_name;

    // Preferences
    public $interest_rate;

    public $grace_period;

    public $currency = 'NGN';

    public $allow_flexible_repayments = false;

    public function mount()
    {
        $this->organization = Auth::user()->organization;

        if ($this->organization) {
            $this->name = $this->organization->name;
            $this->tagline = $this->organization->tagline;
            $this->rc_number = $this->organization->rc_number;
            $this->email = $this->organization->email;
            $this->phone = $this->organization->phone;
            $this->address = $this->organization->address;
            $this->website = $this->organization->website;
            $this->currentLogo = $this->organization->logo_path;
            $this->currentSignature = $this->organization->signature_path;

            $this->brand_color = $this->organization->brand_color ?? '#0f172a';

            $this->repayment_bank_name = $this->organization->repayment_bank_name;
            $this->repayment_account_number = $this->organization->repayment_account_number;
            $this->repayment_account_name = $this->organization->repayment_account_name;

            $this->interest_rate = $this->organization->default_interest_rate;
            $this->grace_period = $this->organization->grace_period_days;
            $this->currency = $this->organization->currency_code ?? 'NGN';
            $this->allow_flexible_repayments = $this->organization->allow_flexible_repayments;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:100',
            'brand_color' => 'required|string|max:7',
            'repayment_bank_name' => 'nullable|string|max:100',
            'repayment_account_number' => 'nullable|string|max:20',
            'repayment_account_name' => 'nullable|string|max:100',
            'interest_rate' => 'required|numeric|min:0',
        ]);

        $data = [
            'name' => $this->name,
            'tagline' => $this->tagline,
            'rc_number' => $this->rc_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'brand_color' => $this->brand_color,
            'repayment_bank_name' => $this->repayment_bank_name,
            'repayment_account_number' => $this->repayment_account_number,
            'repayment_account_name' => $this->repayment_account_name,
            'default_interest_rate' => $this->interest_rate,
            'grace_period_days' => $this->grace_period,
            'allow_flexible_repayments' => $this->allow_flexible_repayments,
        ];

        if ($this->logo) {
            $filename = \Illuminate\Support\Str::random(40).'.'.$this->logo->getClientOriginalExtension();
            $path = 'logos/'.$filename;
            $stream = fopen($this->logo->getRealPath(), 'r');
            $disk = env('SUPABASE_URL') ? 'supabase' : config('filesystems.default');
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $data['logo_path'] = $path;
        }

        if ($this->signature) {
            $filename = \Illuminate\Support\Str::random(40).'.'.$this->signature->getClientOriginalExtension();
            $path = 'signatures/'.$filename;
            $stream = fopen($this->signature->getRealPath(), 'r');
            $disk = env('SUPABASE_URL') ? 'supabase' : config('filesystems.default');
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $data['signature_path'] = $path;
        }

        if ($this->kyc_document) {
            $filename = \Illuminate\Support\Str::random(40).'.'.$this->kyc_document->getClientOriginalExtension();
            $path = 'kyc-docs/'.$filename;
            $stream = fopen($this->kyc_document->getRealPath(), 'r');
            $disk = env('SUPABASE_URL') ? 'supabase' : config('filesystems.default');
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $data['kyc_document_path'] = $path;
        }

        $this->organization->update($data);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Settings updated successfully.']);
    }

    public function render()
    {
        return view('livewire.settings.general-settings')->layout('layouts.app', ['title' => 'General Settings']);
    }
}

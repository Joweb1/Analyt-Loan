<?php

namespace App\Livewire\Settings;

use App\Services\SystemMaintenanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public $organization;

    // Basic Info
    public $name;

    public $tagline;

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
    public $brand_color = '#0f172a';

    // Repayment Bank Details
    public $repayment_bank_name;

    public $repayment_account_number;

    public $repayment_account_name;

    // Preferences
    public $interest_rate;

    public $grace_period;

    public $currency = 'NGN';

    public $timezone;

    public $allow_flexible_repayments = false;

    // Time Control (NEW)
    public $use_manual_date = false;

    public $operating_date;

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
            $this->timezone = $this->organization->timezone ?? 'UTC';
            $this->allow_flexible_repayments = $this->organization->allow_flexible_repayments;

            $this->use_manual_date = $this->organization->use_manual_date;
            $this->operating_date = $this->organization->operating_date
                ? \Carbon\Carbon::parse($this->organization->operating_date)->format('Y-m-d')
                : \Illuminate\Support\Carbon::now($this->timezone)->format('Y-m-d');
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
            'operating_date' => 'required_if:use_manual_date,true|date',
            'timezone' => 'required|string',
        ]);

        $oldManualDate = $this->organization->use_manual_date;
        $oldOperatingDate = $this->organization->operating_date ? $this->organization->operating_date->startOfDay() : null;
        $newOperatingDate = Carbon::parse($this->operating_date, $this->timezone)->startOfDay();

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
            'use_manual_date' => $this->use_manual_date,
            'operating_date' => $this->use_manual_date ? $newOperatingDate : null,
            'timezone' => $this->timezone,
        ];

        // Handle File Uploads
        if ($this->logo) {
            $filename = \Illuminate\Support\Str::random(40).'.'.$this->logo->getClientOriginalExtension();
            $path = 'logos/'.$filename;
            $stream = fopen($this->logo->getRealPath(), 'r');
            $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $data['logo_path'] = $path;
        }

        // ... signature and kyc_document logic (omitted for brevity in replace, but keeping in full write)
        if ($this->signature) {
            $filename = \Illuminate\Support\Str::random(40).'.'.$this->signature->getClientOriginalExtension();
            $path = 'signatures/'.$filename;
            $stream = fopen($this->signature->getRealPath(), 'r');
            $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');
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
            $disk = config('filesystems.disks.supabase.is_configured') ? 'supabase' : config('filesystems.default');
            \Illuminate\Support\Facades\Storage::disk($disk)->put($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
            $data['kyc_document_path'] = $path;
        }

        $this->organization->update($data);

        // Immediate Sync Trigger for Skipped Days
        if ($this->use_manual_date) {
            // Case 1: Switching from real-time to manual, or manual date changed forward
            $startDate = $oldOperatingDate ?? \Illuminate\Support\Carbon::now($this->timezone)->startOfDay();

            if ($newOperatingDate->isAfter($startDate)) {
                $days = (int) $startDate->diffInDays($newOperatingDate);

                for ($i = 1; $i <= $days; $i++) {
                    $runDate = $startDate->copy()->addDays($i);
                    SystemMaintenanceService::runMaintenanceForDate($this->organization->id, $runDate);
                }
            } elseif ($newOperatingDate->isBefore($startDate)) {
                // Backdating: Just run once for the target date to fix statuses
                SystemMaintenanceService::runMaintenanceForDate($this->organization->id, $newOperatingDate);
            } else {
                // Same day: Run once to ensure today's maintenance is current
                SystemMaintenanceService::runMaintenanceForDate($this->organization->id, $newOperatingDate);
            }
        }

        // Reset Carbon for the remainder of this request to the actual operating date
        if ($this->use_manual_date && $this->organization->operating_date) {
            \Carbon\Carbon::setTestNow($this->organization->operating_date);
        } else {
            \Carbon\Carbon::setTestNow();
        }

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Settings updated successfully. Time override active.']);

        return $this->redirect(route('settings'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.general-settings')->layout('layouts.app', ['title' => 'General Settings']);
    }
}

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

    public $interest_calculation_type = 'percentage';

    public $grace_period;

    public $currency = 'NGN';

    public $timezone;

    public $allow_flexible_repayments = false;

    public $cashbook_unlock_limit = 3;

    public $allow_staff_cashbook_unlock = true;

    public $thrift_cycle_days = 6;

    public $default_customer_password;

    // Time Control (Simplified)
    public $system_date;

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
            $this->interest_calculation_type = $this->organization->interest_calculation_type ?? 'percentage';
            $this->grace_period = $this->organization->grace_period_days;
            $this->currency = $this->organization->currency_code ?? 'NGN';
            $this->timezone = $this->organization->timezone ?? 'UTC';
            $this->allow_flexible_repayments = $this->organization->allow_flexible_repayments;
            $this->cashbook_unlock_limit = $this->organization->cashbook_unlock_limit ?? 3;
            $this->allow_staff_cashbook_unlock = $this->organization->allow_staff_cashbook_unlock ?? true;
            $this->thrift_cycle_days = $this->organization->thrift_cycle_days ?? 6;
            $this->default_customer_password = $this->organization->default_customer_password;

            $this->system_date = $this->organization->system_date
                ? \Carbon\Carbon::parse($this->organization->system_date)->format('Y-m-d')
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
            'interest_calculation_type' => 'required|in:fixed,percentage',
            'cashbook_unlock_limit' => 'required|integer|min:0',
            'allow_staff_cashbook_unlock' => 'required|boolean',
            'thrift_cycle_days' => 'required|integer|in:5,6',
            'system_date' => 'required|date',
            'timezone' => 'required|string',
            'default_customer_password' => 'required|string|min:4',
        ]);

        $oldSystemDate = $this->organization->system_date ? $this->organization->system_date->startOfDay() : null;
        $newSystemDate = Carbon::parse($this->system_date, $this->timezone)->startOfDay();

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
            'interest_calculation_type' => $this->interest_calculation_type,
            'grace_period_days' => $this->grace_period,
            'thrift_cycle_days' => $this->thrift_cycle_days,
            'allow_flexible_repayments' => $this->allow_flexible_repayments,
            'cashbook_unlock_limit' => $this->cashbook_unlock_limit,
            'allow_staff_cashbook_unlock' => $this->allow_staff_cashbook_unlock,
            'default_customer_password' => $this->default_customer_password,
            'system_date' => $newSystemDate,
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
        if ($oldSystemDate && $newSystemDate->isAfter($oldSystemDate)) {
            $days = (int) $oldSystemDate->diffInDays($newSystemDate);

            for ($i = 1; $i <= $days; $i++) {
                $runDate = $oldSystemDate->copy()->addDays($i);
                SystemMaintenanceService::runMaintenanceForDate($this->organization->id, $runDate);
            }
        } elseif ($newSystemDate->isBefore($oldSystemDate)) {
            // Backdating: Just run once for the target date to fix statuses
            SystemMaintenanceService::runMaintenanceForDate($this->organization->id, $newSystemDate);
        } else {
            // Same day or first initialization: Run once to ensure today's maintenance is current
            SystemMaintenanceService::runMaintenanceForDate($this->organization->id, $newSystemDate);
        }

        // Apply simulation to current request
        \Carbon\Carbon::setTestNow($this->organization->getSystemTime());

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Settings updated successfully. System date changed to '.$newSystemDate->format('M d, Y')]);

        return $this->redirect(route('settings'), navigate: true);
    }

    public function render()
    {
        return view('livewire.settings.general-settings')->layout('layouts.app', ['title' => 'General Settings']);
    }
}

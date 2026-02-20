<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserProfile extends Component
{
    // Profile Data
    public $name;

    public $email;

    public $phone;

    public $role;

    public $organization;

    public $created_at;

    public $last_seen_at;

    // Password Update
    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    // Stats
    public $loans_assigned_count = 0;

    public $customers_managed_count = 0;

    public $days_active_string = '';

    public $profile_strength = 0;

    // Borrower / KYC Data
    public $is_borrower = false;

    public $borrower;

    public $kyc_status = 'pending';

    // KYC Fields for completion
    public $dob;

    public $gender;

    public $address;

    public $bvn;

    public $nin;

    public $marital_status;

    public $dependents;

    public $employment_information;

    public $bank_account_details;

    // Activity Log
    public $activity_logs = [];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->getRoleNames()->first() ?? 'User';
        $this->organization = $user->organization->name ?? 'None';
        $this->created_at = $user->created_at->format('M d, Y');
        $this->last_seen_at = $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never';

        $this->is_borrower = $user->hasRole('Borrower');

        if ($this->is_borrower) {
            $this->borrower = Borrower::where('user_id', $user->id)->first();
            if ($this->borrower) {
                $this->kyc_status = $this->borrower->kyc_status;
                $this->dob = $this->borrower->date_of_birth;
                $this->gender = $this->borrower->gender;
                $this->address = $this->borrower->address;
                $this->bvn = $this->borrower->bvn;
                $this->nin = $this->borrower->national_identity_number;
                $this->marital_status = $this->borrower->marital_status;
                $this->dependents = $this->borrower->dependents;
                $this->employment_information = $this->borrower->employment_information;
                $this->bank_account_details = $this->borrower->bank_account_details;
            }
        }

        // Calculate Stats
        $this->loans_assigned_count = $user->assignedLoans()->count();
        $this->customers_managed_count = Loan::where('loan_officer_id', $user->id)->distinct('borrower_id')->count('borrower_id');

        // Detailed Days Active
        $diff = $user->created_at->diff(now());
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = "{$diff->y}Yr";
        }
        if ($diff->m > 0) {
            $parts[] = "{$diff->m}Mth";
        }
        $parts[] = "{$diff->d}Days";
        $this->days_active_string = implode(':', $parts).":{$diff->h}Hr:{$diff->i}mins";

        // Profile Strength Calculation
        $fields = ['name', 'email', 'phone', 'last_login_at', 'settings'];
        if ($this->is_borrower) {
            $fields = array_merge($fields, ['dob', 'gender', 'address', 'bvn', 'nin']);
        }

        $filled = 0;
        foreach ($fields as $field) {
            if ($this->is_borrower && in_array($field, ['dob', 'gender', 'address', 'bvn', 'nin'])) {
                if ($this->borrower && ! empty($this->borrower->$field)) {
                    $filled++;
                }
            } else {
                if (! empty($user->$field)) {
                    $filled++;
                }
            }
        }
        $this->profile_strength = round(($filled / count($fields)) * 100);

        // Fetch Activity Logs (Where user is the actor)
        $this->activity_logs = SystemNotification::where('user_id', $user->id)
            ->latest()
            ->take(15)
            ->get();
    }

    public function completeKyc()
    {
        $this->validate([
            'dob' => 'required|date',
            'gender' => 'required|string',
            'address' => 'required|string',
            'bvn' => 'required|string|size:11',
            'nin' => 'required|string|size:11',
            'marital_status' => 'required|string',
            'bank_account_details' => 'required|string',
        ]);

        if (! $this->borrower) {
            $this->borrower = Borrower::create([
                'user_id' => Auth::id(),
                'organization_id' => Auth::user()->organization_id,
                'phone' => Auth::user()->phone,
                'kyc_status' => 'pending',
            ]);
        }

        $this->borrower->update([
            'date_of_birth' => $this->dob,
            'gender' => $this->gender,
            'address' => $this->address,
            'bvn' => $this->bvn,
            'national_identity_number' => $this->nin,
            'marital_status' => $this->marital_status,
            'dependents' => $this->dependents,
            'employment_information' => $this->employment_information,
            'bank_account_details' => $this->bank_account_details,
            'kyc_status' => 'pending', // Submit for review
        ]);

        $this->kyc_status = 'pending';
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'KYC information submitted for review.']);
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.Auth::id(),
            // Phone is now read-only as per instructions
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Profile updated successfully.']);
    }

    public function exportLog()
    {
        if (! Auth::user()->hasPermissionTo('export_and_print')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to export logs.']);

            return;
        }

        return redirect()->route('report.print', ['type' => 'staff_activity']);
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Password changed successfully.']);
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }

    public function render()
    {
        return view('livewire.user-profile')->layout('layouts.app', ['title' => 'My Profile']);
    }
}

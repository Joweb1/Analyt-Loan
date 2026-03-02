<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class BorrowerProfile extends Component
{
    use WithFileUploads;

    public Borrower $borrower;

    public $isEditing = false;

    // User Fields
    public $name;

    public $email;

    public $phone;

    // Borrower Fields
    public $bvn;

    public $national_identity_number;

    public $gender;

    public $date_of_birth;

    public $marital_status;

    public $address;

    public $employment_information;

    public $income_proof_path;

    public $new_photo;

    public $photo_url;

    // KYC Approval
    public $kyc_status;

    public $rejection_reason;

    public function mount(Borrower $borrower)
    {
        if (! \Illuminate\Support\Facades\Auth::user()->hasPermissionTo('manage_borrowers')) {
            abort(403);
        }
        $this->borrower = $borrower->load('user');
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->name = $this->borrower->user->name;
        $this->email = $this->borrower->user->email;
        $this->phone = $this->borrower->phone;
        $this->bvn = $this->borrower->bvn;
        $this->national_identity_number = $this->borrower->national_identity_number;
        $this->gender = $this->borrower->gender;
        $this->date_of_birth = $this->borrower->date_of_birth;
        $this->marital_status = $this->borrower->marital_status;
        $this->address = $this->borrower->address;
        $this->employment_information = $this->borrower->employment_information;
        $this->photo_url = $this->borrower->photo_url;
        $this->kyc_status = $this->borrower->kyc_status;
    }

    public function approveKyc()
    {
        if (! \Illuminate\Support\Facades\Auth::user()->hasPermissionTo('manage_borrowers')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized action.']);

            return;
        }

        $this->borrower->update([
            'kyc_status' => 'approved',
            'rejection_reason' => null,
        ]);

        \App\Helpers\SystemLogger::success(
            'KYC Approved',
            "Identity verification for {$this->borrower->user->name} has been approved.",
            'kyc',
            $this->borrower
        );

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'KYC approved successfully.']);
        $this->resolveKycNotifications();

        return redirect()->route('customer');
    }

    public function declineKyc()
    {
        if (! \Illuminate\Support\Facades\Auth::user()->hasPermissionTo('manage_borrowers')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized action.']);

            return;
        }

        $this->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $this->borrower->update([
            'kyc_status' => 'rejected',
            'rejection_reason' => $this->rejection_reason,
        ]);

        \App\Helpers\SystemLogger::danger(
            'KYC Rejected',
            "Identity verification for {$this->borrower->user->name} was rejected. Reason: {$this->rejection_reason}",
            'kyc',
            $this->borrower
        );

        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'KYC has been declined.']);
        $this->kyc_status = 'rejected';
        $this->resolveKycNotifications();
    }

    protected function resolveKycNotifications()
    {
        \App\Models\SystemNotification::where('subject_id', $this->borrower->id)
            ->where('subject_type', Borrower::class)
            ->where('category', 'kyc')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
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
        if (! \Illuminate\Support\Facades\Auth::user()->hasPermissionTo('edit_borrowers')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to edit customer profiles.']);

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->borrower->user_id,
            // phone removed as it's read-only
            'bvn' => 'nullable|string|max:11',
            'national_identity_number' => 'nullable|string|max:11',
            'new_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = $this->borrower->user;
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $borrowerData = [
            // phone removed as it's read-only
            'bvn' => $this->bvn,
            'national_identity_number' => $this->national_identity_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'marital_status' => $this->marital_status,
            'address' => $this->address,
            'employment_information' => $this->employment_information,
        ];

        if ($this->new_photo) {
            $path = $this->new_photo->store('borrower-photos');
            $borrowerData['photo_url'] = $path;

            // Allow model accessor to handle disk-aware URL generation
            $this->borrower->update($borrowerData);
            $this->photo_url = $this->borrower->fresh()->photo_url;
        } else {
            $this->borrower->update($borrowerData);
        }

        $this->isEditing = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Profile updated successfully.']);
    }

    public function render()
    {
        return view('livewire.borrower-profile')->layout('layouts.app', ['title' => 'Borrower Profile']);
    }
}

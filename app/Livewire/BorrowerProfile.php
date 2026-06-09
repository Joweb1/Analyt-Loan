<?php

namespace App\Livewire;

use App\Helpers\SystemLogger;
use App\Models\Borrower;
use App\Models\SystemNotification;
use App\Models\User;
use App\Traits\HandlesStorageDisk;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class BorrowerProfile extends Component
{
    use HandlesStorageDisk, WithFileUploads;

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

    public $collection_group;

    public $is_daily_saver;

    public $daily_target_amount;

    public $employment_information = [];

    public $bank_account_details = [];

    public $next_of_kin_details = [];

    public $income_proof_path;

    public $new_photo;

    public $photo_url;

    // Document Uploads
    public $passport_photo;

    public $identity_doc;

    public $bank_stmt;

    public $income_proof;

    // KYC Approval
    public $kyc_status;

    public $rejection_reason;

    public $confirmingDeletion = false;

    public function mount(Borrower $borrower)
    {
        if (! Auth::user()->hasPermissionTo('manage_borrowers')) {
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
        $this->collection_group = $this->borrower->collection_group;
        $this->is_daily_saver = $this->borrower->is_daily_saver;
        $this->daily_target_amount = $this->borrower->daily_target_amount ? $this->borrower->daily_target_amount->getMajorAmount() : 0;

        $this->employment_information = is_array($this->borrower->employment_information)
            ? $this->borrower->employment_information
            : ['employer_name' => '', 'job_title' => '', 'monthly_income' => 0, 'employment_status' => '', 'employer_address' => ''];

        $this->bank_account_details = is_array($this->borrower->bank_account_details)
            ? $this->borrower->bank_account_details
            : ['bank_name' => '', 'account_number' => '', 'account_name' => ''];

        $this->next_of_kin_details = is_array($this->borrower->next_of_kin_details)
            ? $this->borrower->next_of_kin_details
            : ['name' => '', 'relationship' => '', 'phone' => ''];

        $this->photo_url = $this->borrower->photo_url;
        $this->kyc_status = $this->borrower->kyc_status;
    }

    public function approveKyc()
    {
        if (! Auth::user()->hasPermissionTo('manage_borrowers')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized action.']);

            return;
        }

        $this->borrower->update([
            'kyc_status' => 'approved',
            'rejection_reason' => null,
        ]);

        SystemLogger::success(
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
        if (! Auth::user()->hasPermissionTo('manage_borrowers')) {
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

        SystemLogger::danger(
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
        SystemNotification::where('subject_id', $this->borrower->id)
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

    public function deleteCustomer()
    {
        if (! Auth::user()->isAdmin()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Only administrators can delete customers.']);

            return;
        }

        $user = $this->borrower->user;
        $name = $user->name;

        // Deleting the user should trigger cascade deletes for borrower, saver, guarantor, etc.
        $user->delete();

        SystemLogger::danger(
            'Customer Deleted',
            "The customer account for {$name} and all related data have been permanently deleted.",
            'customer_management'
        );

        session()->flash('custom-alert', ['type' => 'warning', 'message' => "Customer {$name} and all related records have been deleted."]);

        return redirect()->route('customer');
    }

    public function save()
    {
        if (! Auth::user()->hasPermissionTo('edit_borrowers')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to edit customer profiles.']);

            return;
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->borrower->user_id,
            'bvn' => 'nullable|string|max:11',
            'national_identity_number' => 'nullable|string|max:11',
            'new_photo' => ['nullable', 'image', 'max:2048'],
            'passport_photo' => ['nullable', 'image', 'max:2048'],
            'identity_doc' => ['nullable', 'file', 'max:5120'],
            'bank_stmt' => ['nullable', 'file', 'max:10240'],
            'income_proof' => ['nullable', 'file', 'max:5120'],
            'bank_account_details.bank_name' => 'nullable|string',
            'bank_account_details.account_number' => 'nullable|string|max:15',
            'bank_account_details.account_name' => 'nullable|string',
        ]);

        $user = $this->borrower->user;
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $borrowerData = [
            'bvn' => $this->bvn,
            'national_identity_number' => $this->national_identity_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'marital_status' => $this->marital_status,
            'address' => $this->address,
            'collection_group' => $this->collection_group,
            'is_daily_saver' => $this->is_daily_saver,
            'daily_target_amount' => $this->daily_target_amount,
            'employment_information' => $this->employment_information,
            'bank_account_details' => $this->bank_account_details,
            'next_of_kin_details' => $this->next_of_kin_details,
        ];

        $disk = $this->getStorageDisk();

        if ($this->new_photo) {
            $path = $this->new_photo->store('borrower-photos', $disk);
            $borrowerData['photo_url'] = $path;
        }

        if ($this->passport_photo) {
            $borrowerData['passport_photograph'] = $this->passport_photo->store('passports', $disk);
        }

        if ($this->identity_doc) {
            $borrowerData['identity_document'] = $this->identity_doc->store('identity-documents', $disk);
        }

        if ($this->bank_stmt) {
            $borrowerData['bank_statement'] = $this->bank_stmt->store('bank-statements', $disk);
        }

        if ($this->income_proof) {
            $borrowerData['income_proof'] = $this->income_proof->store('income-proofs', $disk);
        }

        $this->borrower->update($borrowerData);

        if ($this->new_photo) {
            $this->photo_url = $this->borrower->fresh()->photo_url;
        }

        $this->isEditing = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Profile updated successfully.']);
    }

    public function render()
    {
        return view('livewire.borrower-profile')->layout('layouts.app', ['title' => 'Borrower Profile']);
    }
}

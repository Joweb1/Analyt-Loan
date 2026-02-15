<?php

namespace App\Livewire;

use App\Models\Borrower;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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

    public function mount(Borrower $borrower)
    {
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
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if (!$this->isEditing) {
            $this->loadFields();
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->borrower->user_id,
            'phone' => 'required|string|max:20',
            'bvn' => 'nullable|string|max:11',
            'national_identity_number' => 'nullable|string|max:11',
            'new_photo' => 'nullable|image|max:2048',
        ]);

        $user = $this->borrower->user;
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $borrowerData = [
            'phone' => $this->phone,
            'bvn' => $this->bvn,
            'national_identity_number' => $this->national_identity_number,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'marital_status' => $this->marital_status,
            'address' => $this->address,
            'employment_information' => $this->employment_information,
        ];

        if ($this->new_photo) {
            $path = $this->new_photo->store('borrower-photos', 'public');
            $borrowerData['photo_url'] = Storage::url($path);
            $this->photo_url = $borrowerData['photo_url'];
        }

        $this->borrower->update($borrowerData);

        $this->isEditing = false;
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Profile updated successfully.']);
    }

    public function render()
    {
        return view('livewire.borrower-profile')->layout('layouts.app');
    }
}

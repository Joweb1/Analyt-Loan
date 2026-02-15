<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class OrgRegistrationForm extends Component
{
    use WithFileUploads;

    public $orgName;
    public $orgEmail;
    public $orgLogo;
    
    public $adminName;
    public $phone;
    public $email;
    public $password;
    public $password_confirmation;

    public function save()
    {
        $this->validate([
            'orgName' => 'required|string|max:255',
            'orgEmail' => 'required|email|max:255',
            'orgLogo' => 'nullable|image|max:2048',
            'adminName' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $logoPath = null;
        if ($this->orgLogo) {
            $logoPath = $this->orgLogo->store('logos', 'public');
        }

        // Create Org
        $org = Organization::create([
            'name' => $this->orgName,
            'email' => $this->orgEmail,
            'slug' => \Illuminate\Support\Str::slug($this->orgName),
            'logo_path' => $logoPath,
            'status' => 'active', // Allow login
            'kyc_status' => 'pending', // Needs approval
        ]);

        // Create Admin User
        $user = User::create([
            'organization_id' => $org->id,
            'name' => $this->adminName,
            'phone' => $this->phone,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);
        
        // Link owner
        $org->owner_id = $user->id;
        $org->save();

        // Assign Role
        $adminRole = Role::findByName('Admin');
        if ($adminRole) {
            $user->assignRole($adminRole);
        }

        // Login
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.org-registration-form')->layout('layouts.guest'); // Assuming Guest layout exists
    }
}

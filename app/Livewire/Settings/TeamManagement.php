<?php

namespace App\Livewire\Settings;

use App\Models\User;
use App\Models\Borrower;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeamManagement extends Component
{
    use WithPagination;

    public $showInviteModal = false;
    
    // Invite/Edit Form State
    public $memberId;
    public $name;
    public $email;
    public $phone;
    public $role;
    
    // Search for existing borrowers to promote
    public $searchBorrower = '';
    public $borrowerResults = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'nullable|email',
        'role' => 'required|exists:roles,name',
    ];

    public function updatedSearchBorrower()
    {
        if (strlen($this->searchBorrower) < 3) {
            $this->borrowerResults = [];
            return;
        }

        $orgId = Auth::user()->organization_id;
        $this->borrowerResults = User::role('Borrower')
            ->where('organization_id', $orgId)
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchBorrower . '%')
                  ->orWhere('phone', 'like', '%' . $this->searchBorrower . '%');
            })
            ->take(5)
            ->get();
    }

    public function selectBorrower($id)
    {
        $user = User::find($id);
        $this->name = $user->name;
        $this->phone = $user->phone;
        $this->email = $user->email;
        $this->searchBorrower = '';
        $this->borrowerResults = [];
    }

    public function inviteMember()
    {
        $this->validate();
        $orgId = Auth::user()->organization_id;

        // Check if user exists by phone
        $user = User::where('phone', $this->phone)->first();

        if ($user) {
            if ($user->organization_id !== $orgId) {
                $this->addError('phone', 'This user belongs to another organization.');
                return;
            }
            // Update role if exists
            $user->syncRoles([$this->role]);
            $user->name = $this->name;
            $user->email = $this->email;
            $user->save();
            $message = 'User role updated successfully.';
        } else {
            // Create new staff
            $user = User::create([
                'organization_id' => $orgId,
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'password' => Hash::make('password'), // Default password, they should reset it
            ]);
            $user->assignRole($this->role);
            $message = 'Team member invited successfully.';
        }

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => $message]);
        $this->reset(['showInviteModal', 'name', 'email', 'phone', 'role', 'memberId']);
    }

    public function deleteMember($id)
    {
        $user = User::find($id);
        if ($user->id === Auth::id()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You cannot delete yourself.']);
            return;
        }
        
        // Don't actually delete, just strip roles or move to borrower?
        // For simplicity in this prompt, we'll strip administrative roles
        $user->syncRoles(['Borrower']);
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Member administrative access revoked.']);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;
        $members = User::where('organization_id', $orgId)
            ->whereHas('roles', function($q) {
                $q->whereNotIn('name', ['Borrower']);
            })
            ->withCount(['assignedLoans as assigned_loans_count'])
            ->paginate(10);

        $roles = Role::whereNotIn('name', ['Borrower'])->get();

        return view('livewire.settings.team-management', [
            'members' => $members,
            'roles' => $roles
        ])->layout('layouts.app');
    }
}

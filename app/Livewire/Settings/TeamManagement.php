<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class TeamManagement extends Component
{
    use WithPagination;

    public $showInviteModal = false;

    // Staff creation state
    public $selectedUserId;

    public $selectedUserName;

    public $role;

    // Search for existing users in organization
    public $searchUser = '';

    public $userResults = [];

    protected $rules = [
        'selectedUserId' => 'required|exists:users,id',
        'role' => 'required|exists:roles,name',
    ];

    public function updatedSearchUser()
    {
        if (strlen($this->searchUser) < 2) {
            $this->userResults = [];

            return;
        }

        $orgId = Auth::user()->organization_id;

        // Find users in the same organization who are currently just customers (borrowers/savers)
        $this->userResults = User::where('organization_id', $orgId)
            ->where('type', 'customer')
            ->where(function ($q) {
                $q->where('name', 'like', '%'.$this->searchUser.'%')
                    ->orWhere('phone', 'like', '%'.$this->searchUser.'%')
                    ->orWhere('email', 'like', '%'.$this->searchUser.'%');
            })
            ->take(10)
            ->get();
    }

    public function selectUser($id, $name)
    {
        $this->selectedUserId = $id;
        $this->selectedUserName = $name;
        $this->searchUser = $name;
        $this->userResults = [];
    }

    public function addMember()
    {
        $this->validate();

        $user = User::findOrFail($this->selectedUserId);

        // Ensure user belongs to the same organization
        if ($user->organization_id !== Auth::user()->organization_id) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Unauthorized operation.']);

            return;
        }

        // Promote to Staff
        $user->update(['type' => 'staff']);
        $user->syncRoles([$this->role]);

        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "{$user->name} has been promoted to the team as {$this->role}."]);

        $this->reset(['showInviteModal', 'selectedUserId', 'selectedUserName', 'role', 'searchUser', 'userResults']);
    }

    public function changeRole($userId, $newRole)
    {
        if ($userId === Auth::id()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You cannot change your own role.']);

            return;
        }

        $user = User::findOrFail($userId);

        if ($user->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $user->syncRoles([$newRole]);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "Role for {$user->name} updated to {$newRole}."]);
    }

    public function removeStaffAccess($id)
    {
        $user = User::find($id);
        if ($user->id === Auth::id()) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You cannot revoke your own access.']);

            return;
        }

        // Revert to Customer and default role Saver
        $user->update(['type' => 'customer']);
        $user->syncRoles(['Saver']);

        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => "Administrative access revoked for {$user->name}. They are now a regular customer (Saver)."]);
    }

    public function togglePush($userId)
    {
        $user = User::findOrFail($userId);
        if ($user->organization_id !== Auth::user()->organization_id) {
            return;
        }

        $settings = $user->settings ?? [];
        $settings['push_enabled'] = ! ($settings['push_enabled'] ?? true);
        $user->settings = $settings;
        $user->save();

        $status = $settings['push_enabled'] ? 'enabled' : 'disabled';
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => "Push notifications {$status} for {$user->name}."]);
    }

    public function render()
    {
        $orgId = Auth::user()->organization_id;

        // Members are those of type 'staff' or 'admin'
        $members = User::where('organization_id', $orgId)
            ->whereIn('type', ['staff', 'admin'])
            ->withCount(['assignedLoans as assigned_loans_count'])
            ->paginate(10);

        // Roles available for staff (excluding customer roles)
        $roles = Role::whereNotIn('name', ['Borrower', 'Saver', 'Guarantor', 'App Owner'])->get();

        return view('livewire.settings.team-management', [
            'members' => $members,
            'roles' => $roles,
        ])->layout('layouts.app', ['title' => 'Team Management']);
    }
}

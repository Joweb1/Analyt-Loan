<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class RolesManagement extends Component
{
    public $roleName;
    public $selectedPermissions = [];
    public $editingRoleId;

    public $allPermissions = [
        'view_dashboard',
        'manage_borrowers',
        'manage_loans',
        'approve_loans',
        'manage_collections',
        'view_reports',
        'manage_vault',
        'manage_settings',
    ];

    public function mount()
    {
        // Ensure permissions exist in DB
        foreach ($this->allPermissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
    }

    public function saveRole()
    {
        $this->validate([
            'roleName' => 'required|string|unique:roles,name' . ($this->editingRoleId ? ',' . $this->editingRoleId : ''),
        ]);

        if ($this->editingRoleId) {
            $role = Role::findById($this->editingRoleId);
            $role->name = $this->roleName;
            $role->save();
        } else {
            $role = Role::create(['name' => $this->roleName]);
        }

        $role->syncPermissions($this->selectedPermissions);

        $this->reset(['roleName', 'selectedPermissions', 'editingRoleId']);
        $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Role saved successfully.']);
    }

    public function cancelEdit()
    {
        $this->reset(['roleName', 'selectedPermissions', 'editingRoleId']);
    }

    public function editRole($id)
    {
        $role = Role::findById($id);
        
        // Prevent editing system roles if needed
        if (in_array($role->name, ['Admin', 'Borrower'])) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Cannot edit system roles.']);
            return;
        }

        $this->editingRoleId = $id;
        $this->roleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    public function deleteRole($id)
    {
        $role = Role::findById($id);
        if (in_array($role->name, ['Admin', 'Borrower'])) {
             $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'Cannot delete system roles.']);
            return;
        }
        $role->delete();
        $this->dispatch('custom-alert', ['type' => 'warning', 'message' => 'Role deleted.']);
    }

    public function render()
    {
        return view('livewire.settings.roles-management', [
            'roles' => Role::whereNotIn('name', ['Admin', 'Borrower'])->get()
        ])->layout('layouts.app');
    }
}

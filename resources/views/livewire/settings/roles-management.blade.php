<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h2 class="text-xl font-bold dark:text-white mb-6">Staff Roles & Permissions</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Role List -->
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">Existing Roles</h3>
                        <div class="space-y-2">
                            @foreach($roles as $role)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-zinc-800 rounded-lg">
                                    <span class="font-medium dark:text-white">{{ $role->name }}</span>
                                    <div class="flex gap-2">
                                        <button wire:click="editRole('{{ $role->id }}')" class="text-primary hover:underline text-xs font-bold uppercase">Edit</button>
                                        <button wire:click="deleteRole('{{ $role->id }}')" class="text-red-500 hover:underline text-xs font-bold uppercase">Delete</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Add/Edit Form -->
                    <div class="bg-gray-50 dark:bg-zinc-800 p-6 rounded-xl">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">{{ $editingRoleId ? 'Edit' : 'Create' }} Role</h3>
                        <form wire:submit.prevent="saveRole" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Role Name</label>
                                <input wire:model="roleName" type="text" class="w-full rounded-lg border-gray-300 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white" placeholder="e.g. Loan Officer">
                                @error('roleName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Permissions</label>
                                <div class="grid grid-cols-1 gap-2">
                                    @foreach($allPermissions as $perm)
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm }}" class="rounded border-gray-300 text-primary focus:ring-primary">
                                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ ucwords(str_replace('_', ' ', $perm)) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-4 flex gap-3">
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg font-bold text-sm shadow-lg shadow-primary/20">
                                    {{ $editingRoleId ? 'Update Role' : 'Create Role' }}
                                </button>
                                @if($editingRoleId)
                                    <button type="button" wire:click="cancelEdit" class="text-gray-500 font-bold text-sm">Cancel</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

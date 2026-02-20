<div class="max-w-7xl mx-auto w-full">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Roles & Permissions</h1>
        <p class="text-gray-500 mt-1">Define access levels for your organization staff.</p>
    </div>

    <x-settings-nav active="roles" />

    <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-zinc-800">
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Role List -->
                <div>
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-6">Existing Staff Roles</h3>
                    <div class="space-y-3">
                        @foreach($roles as $role)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-2xl border border-transparent hover:border-primary/10 transition-all group">
                                <span class="font-bold text-primary dark:text-white">{{ $role->name }}</span>
                                <div class="flex gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button wire:click="editRole('{{ $role->id }}')" class="p-2 text-primary hover:bg-white dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    @unless(in_array($role->name, $systemRoles))
                                        <button wire:click="deleteRole('{{ $role->id }}')" class="p-2 text-red-500 hover:bg-white dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    @endunless
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Add/Edit Form -->
                <div class="bg-slate-50 dark:bg-zinc-800/30 p-8 rounded-[2rem] border border-slate-100 dark:border-zinc-800">
                    <h3 class="text-sm font-black uppercase tracking-widest text-primary dark:text-white mb-8">{{ $editingRoleId ? 'Edit' : 'Create New' }} Role</h3>
                    <form wire:submit.prevent="saveRole" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Role Designation Name</label>
                            <input wire:model="roleName" type="text" 
                                class="w-full rounded-xl border-gray-200 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white focus:ring-primary py-3 px-4 font-bold @if(in_array($roleName, $systemRoles)) opacity-60 cursor-not-allowed @endif" 
                                placeholder="e.g. Loan Officer"
                                @if(in_array($roleName, $systemRoles)) readonly @endif>
                            @if(in_array($roleName, $systemRoles))
                                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1 px-1">System role names cannot be modified</p>
                            @endif
                            @error('roleName') <span class="text-red-500 text-xs font-bold mt-1 block px-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-4 px-1">Assign Permissions</label>
                            <div class="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                @foreach($allPermissions as $perm)
                                    <label class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-900 rounded-xl border border-slate-100 dark:border-zinc-800 cursor-pointer hover:border-primary/20 transition-all">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $perm }}" class="rounded-md border-gray-300 text-primary focus:ring-primary size-5">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-tight">{{ str_replace('_', ' ', $perm) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-6 flex flex-col gap-3">
                            <button type="submit" class="w-full bg-primary text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                                {{ $editingRoleId ? 'Update Role Access' : 'Create Access Role' }}
                            </button>
                            @if($editingRoleId)
                                <button type="button" wire:click="cancelEdit" class="w-full py-2 text-xs font-black uppercase text-slate-400 hover:text-primary transition-colors tracking-widest text-center">Cancel Edit</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

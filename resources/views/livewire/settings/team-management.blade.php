<div class="p-0 max-w-7xl mx-auto w-full">
    <!-- Page Heading -->
    <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight text-primary dark:text-white">Team Management</h2>
            <p class="text-[#716b80] text-base font-medium">Manage your organization's administrative members and access levels.</p>
        </div>
        <button wire:click="$set('showInviteModal', true)" class="flex items-center gap-2 bg-primary dark:bg-zinc-100 dark:text-primary text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform active:scale-95">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            <span>Add Team Member</span>
        </button>
    </div>

    <x-settings-nav active="team" />

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-[#dfdee3] dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-primary/5 dark:bg-zinc-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Member</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-center">Push Notifs</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Assigned Loans</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[#dfdee3] dark:divide-zinc-800">
                @foreach($members as $member)
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-primary flex items-center justify-center text-white font-bold relative">
                                    {{ substr($member->name, 0, 1) }}
                                    <div class="absolute -bottom-0.5 -right-0.5 size-3.5 rounded-full border-2 border-white dark:border-zinc-900 {{ $member->isOnline() ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'bg-gray-300' }}"></div>
                                </div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-bold dark:text-white">{{ $member->name }}</p>
                                    <p class="text-xs text-[#716b80]">{{ $member->phone }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <select 
                                wire:change="changeRole('{{ $member->id }}', $event.target.value)"
                                class="bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border-none rounded-full text-xs font-bold py-1 px-3 focus:ring-1 focus:ring-blue-500 cursor-pointer"
                            >
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" {{ $member->hasRole($r->name) ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-6 py-5 text-center">
                            @php $isPushEnabled = $member->pushEnabled(); @endphp
                            <button wire:click="togglePush('{{ $member->id }}')" class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isPushEnabled ? 'bg-primary' : 'bg-gray-200 dark:bg-zinc-700' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isPushEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-sm font-bold dark:text-white">{{ $member->assigned_loans_count }} Loans</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button 
                                    wire:click="removeStaffAccess('{{ $member->id }}')" 
                                    wire:confirm="Are you sure you want to revoke administrative access for {{ $member->name }}?"
                                    class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Revoke Admin Access"
                                >
                                    <span class="material-symbols-outlined text-[20px]">person_remove</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-[#dfdee3] dark:border-zinc-800">
            {{ $members->links() }}
        </div>
    </div>

    <!-- Add Member Modal -->
    @if($showInviteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-[520px] rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                <div class="px-8 pt-8 pb-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-primary dark:text-white text-2xl font-black">Promote to Team</h2>
                        <p class="text-gray-500 text-sm">Select an existing user to grant team access.</p>
                    </div>
                    <button wire:click="$set('showInviteModal', false)" class="text-gray-400 hover:text-primary">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="px-8 py-4 space-y-6">
                    <!-- User Search -->
                    <div class="relative">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Search User/Borrower</label>
                        <div class="relative">
                            <input 
                                wire:model.live.debounce.300ms="searchUser" 
                                type="text" 
                                class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white h-14 pl-12 pr-4 focus:ring-primary focus:border-primary" 
                                placeholder="Search by name, phone or email..."
                                autocomplete="off"
                            >
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                        </div>

                        @if(!empty($userResults))
                            <div class="absolute z-10 w-full mt-2 bg-white dark:bg-zinc-800 border dark:border-zinc-700 rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto">
                                @foreach($userResults as $res)
                                    <button 
                                        wire:click="selectUser('{{ $res->id }}', '{{ $res->name }}')" 
                                        class="w-full px-5 py-3 text-left hover:bg-primary/5 dark:hover:bg-zinc-700 border-b dark:border-zinc-700 last:border-0 transition-colors group"
                                    >
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-bold dark:text-white group-hover:text-primary">{{ $res->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $res->phone }} | {{ $res->email ?? 'No Email' }}</p>
                                            </div>
                                            <span class="material-symbols-outlined text-gray-300 text-[20px]">add_circle</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @elseif($searchUser && strlen($searchUser) >= 2 && empty($userResults) && !$selectedUserId)
                             <div class="absolute z-10 w-full mt-2 bg-white dark:bg-zinc-800 border dark:border-zinc-700 rounded-xl p-4 text-center text-sm text-gray-500 shadow-xl">
                                No users found in your organization.
                             </div>
                        @endif
                        @error('selectedUserId') <span class="text-red-500 text-xs mt-1 block">Please select a user from the results.</span> @enderror
                    </div>

                    @if($selectedUserId)
                        <div class="bg-primary/5 dark:bg-zinc-800 p-4 rounded-xl border border-primary/10 flex items-center gap-3">
                            <div class="size-10 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold">
                                {{ substr($selectedUserName, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Selected User</p>
                                <p class="text-sm font-black text-primary dark:text-white">{{ $selectedUserName }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Assign Access Level</label>
                        <select wire:model="role" class="w-full rounded-xl border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white h-14 px-4 focus:ring-primary focus:border-primary">
                            <option value="">Select a role</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-8 pb-8 pt-4">
                    <button 
                        wire:click="addMember" 
                        class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg hover:bg-primary/90 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                        @if(!$selectedUserId) disabled @endif
                    >
                        Save Team Member
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="flex flex-col gap-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Organization Management</h2>
            <p class="text-slate-500 dark:text-slate-400">Manage all registered entities on the platform.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-[#1a1f2b] p-4 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800 flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by name, email or RC..." class="w-full pl-10 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20">
            </div>
        </div>
        <select wire:model.live="statusFilter" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 px-4 py-2">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
        </select>
        <select wire:model.live="kycFilter" class="bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 px-4 py-2">
            <option value="">All KYC</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <!-- Organizations Table -->
    <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-50 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Organization</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Stats</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">KYC Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Platform Status</th>
                        <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($organizations as $org)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0">
                                        @if($org->logo_path)
                                            <img src="{{ $org->logo_url }}" class="w-full h-full object-contain rounded-lg">
                                        @else
                                            <span class="material-symbols-outlined text-slate-400">business</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $org->name }}</h4>
                                        <p class="text-xs text-slate-500 truncate">{{ $org->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                        {{ $org->borrowers_count }} Borrowers
                                    </span>
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                        {{ $org->loans_count }} Loans
                                    </span>
                                    <span class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                        {{ $org->staff_count }} Staff
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($org->kyc_status === 'approved') bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400
                                    @elseif($org->kyc_status === 'pending') bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400
                                    @else bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400
                                    @endif">
                                    {{ $org->kyc_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($org->status === 'active') bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400
                                    @else bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400
                                    @endif">
                                    {{ $org->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="viewDetails('{{ $org->id }}')" class="p-2 text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $organizations->links() }}
        </div>
    </div>

    <!-- Details Modal -->
    @if($showDetailsModal && $selectedOrg)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative w-full max-w-2xl bg-white dark:bg-[#1a1f2b] rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in duration-200">
                <!-- Modal Header -->
                <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center">
                            @if($selectedOrg->logo_path)
                                <img src="{{ $selectedOrg->logo_url }}" class="w-full h-full object-contain rounded-2xl">
                            @else
                                <span class="material-symbols-outlined text-2xl text-slate-400">business</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white">{{ $selectedOrg->name }}</h3>
                            <p class="text-sm text-slate-500 uppercase tracking-widest font-medium">{{ $selectedOrg->rc_number ?? 'RC: N/A' }}</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div>
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Contact Information</h4>
                            <div class="flex flex-col gap-3">
                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="material-symbols-outlined text-primary text-lg">mail</span>
                                    {{ $selectedOrg->email }}
                                </div>
                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="material-symbols-outlined text-primary text-lg">call</span>
                                    {{ $selectedOrg->phone ?? 'N/A' }}
                                </div>
                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                    <span class="material-symbols-outlined text-primary text-lg">location_on</span>
                                    {{ $selectedOrg->address ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Staff Summary</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Total Staff</p>
                                    <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $selectedOrg->staff_count }}</p>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Borrowers</p>
                                    <p class="text-xl font-bold text-slate-800 dark:text-white">{{ $selectedOrg->borrowers_count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff/Admin List -->
                    <div>
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Staff Members & Contact</h4>
                        <div class="flex flex-col gap-4">
                            @foreach($selectedOrg->users as $user)
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center border border-slate-100 dark:border-slate-700">
                                            <span class="material-symbols-outlined text-slate-400">person</span>
                                        </div>
                                        <div>
                                            <h5 class="text-sm font-bold text-slate-800 dark:text-white">{{ $user->name }}</h5>
                                            <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <a href="mailto:{{ $user->email }}" class="p-2 text-primary bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 hover:scale-110 transition-transform">
                                            <span class="material-symbols-outlined text-sm">mail</span>
                                        </a>
                                        @if($user->phone)
                                        <a href="tel:{{ $user->phone }}" class="p-2 text-emerald-500 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 hover:scale-110 transition-transform">
                                            <span class="material-symbols-outlined text-sm">call</span>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-8 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        @if($selectedOrg->kyc_status === 'pending')
                            <button wire:click="approveKyc('{{ $selectedOrg->id }}')" class="px-6 py-2 bg-emerald-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 transition-all">
                                Approve KYC
                            </button>
                        @endif
                        <button wire:click="toggleStatus('{{ $selectedOrg->id }}')" class="px-6 py-2 {{ $selectedOrg->status === 'active' ? 'bg-rose-500' : 'bg-blue-500' }} text-white rounded-xl text-sm font-bold shadow-lg transition-all">
                            {{ $selectedOrg->status === 'active' ? 'Suspend Access' : 'Restore Access' }}
                        </button>
                    </div>
                    <button wire:click="closeModal" class="px-6 py-2 text-slate-500 font-bold text-sm hover:underline">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

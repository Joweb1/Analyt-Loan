<div class="py-12" x-data="{ contactModal: false, selectedOrg: {} }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
                    Distribution Panel
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($organizations as $org)
                        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 border border-gray-200 dark:border-zinc-700 relative group transition hover:shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-zinc-700 flex items-center justify-center overflow-hidden">
                                        @if($org->logo_path)
                                            <img src="{{ Storage::url($org->logo_path) }}" alt="{{ $org->name }}" class="h-full w-full object-cover">
                                        @else
                                            <span class="text-lg font-bold text-gray-500">{{ substr($org->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $org->name }}</h3>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $org->email ?? 'No email' }}</span>
                                    </div>
                                </div>
                                <div class="text-xs font-semibold px-2 py-1 rounded {{ $org->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($org->status) }}
                                </div>
                            </div>

                            <div class="space-y-2 mb-4 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex justify-between">
                                    <span>Lent (Month):</span>
                                    <span class="font-medium">₦{{ number_format($org->monthly_lent ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Collected (Month):</span>
                                    <span class="font-medium">₦{{ number_format($org->monthly_collected ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between border-t border-gray-100 dark:border-zinc-700 pt-2">
                                    <span>Active Customers:</span>
                                    <span class="font-medium">{{ $org->borrowers_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Active Staff:</span>
                                    <span class="font-medium">{{ $org->staff_count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Active Loans:</span>
                                    <span class="font-medium">{{ $org->active_loans_count }}</span>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-zinc-700 pt-4 flex flex-col space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold uppercase text-gray-500">KYC Status</span>
                                    <span class="text-xs font-bold {{ $org->kyc_status === 'approved' ? 'text-green-600' : ($org->kyc_status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                                        {{ ucfirst($org->kyc_status) }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2">
                                    <button wire:click="toggleStatus('{{ $org->id }}')" class="w-full px-3 py-2 text-xs font-bold text-center rounded {{ $org->status === 'active' ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                                        {{ $org->status === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                    
                                    @if($org->kyc_status !== 'approved')
                                        <button wire:click="updateKycStatus('{{ $org->id }}', 'approved')" class="w-full px-3 py-2 text-xs font-bold text-center bg-blue-50 text-blue-600 hover:bg-blue-100 rounded">
                                            Approve KYC
                                        </button>
                                    @else
                                        <button wire:click="updateKycStatus('{{ $org->id }}', 'rejected')" class="w-full px-3 py-2 text-xs font-bold text-center bg-gray-50 text-gray-600 hover:bg-gray-100 rounded">
                                            Reject KYC
                                        </button>
                                    @endif
                                </div>
                                
                                <div class="text-center pt-2">
                                    <button @click="contactModal = true; selectedOrg = {{ json_encode($org) }}" class="text-xs text-primary hover:underline font-bold uppercase tracking-wider">View Contact Details</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Modal -->
    <div x-show="contactModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="contactModal = false"></div>
        <div class="relative w-full max-w-2xl bg-white dark:bg-[#1a1f2b] rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center">
                        <template x-if="selectedOrg.logo_path">
                            <img :src="'/storage/' + selectedOrg.logo_path" class="w-full h-full object-contain rounded-2xl">
                        </template>
                        <template x-if="!selectedOrg.logo_path">
                            <span class="material-symbols-outlined text-2xl text-slate-400">business</span>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white" x-text="selectedOrg.name"></h3>
                        <p class="text-sm text-slate-500 uppercase tracking-widest font-medium" x-text="selectedOrg.rc_number ? 'RC: ' + selectedOrg.rc_number : 'RC: N/A'"></p>
                    </div>
                </div>
                <button @click="contactModal = false" class="p-2 text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-full">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <div class="mb-8">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Organization Contact</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <span class="material-symbols-outlined text-primary text-lg">mail</span>
                            <span x-text="selectedOrg.email"></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <span class="material-symbols-outlined text-primary text-lg">call</span>
                            <span x-text="selectedOrg.phone || 'N/A'"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Staff Members & Admins</h4>
                    <div class="flex flex-col gap-4">
                        <template x-for="user in selectedOrg.users" :key="user.id">
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center border border-slate-100 dark:border-slate-700">
                                        <span class="material-symbols-outlined text-slate-400">person</span>
                                    </div>
                                    <div>
                                        <h5 class="text-sm font-bold text-slate-800 dark:text-white" x-text="user.name"></h5>
                                        <p class="text-xs text-slate-500" x-text="user.email"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <a :href="'mailto:' + user.email" class="p-2 text-primary bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 hover:scale-110 transition-transform">
                                        <span class="material-symbols-outlined text-sm">mail</span>
                                    </a>
                                    <template x-if="user.phone">
                                        <a :href="'tel:' + user.phone" class="p-2 text-emerald-500 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 hover:scale-110 transition-transform">
                                            <span class="material-symbols-outlined text-sm">call</span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-8 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                <button @click="contactModal = false" class="px-6 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/20 hover:scale-105 active:scale-95 transition-all">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

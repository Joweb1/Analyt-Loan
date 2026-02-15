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
                                            <img src="{{ asset('storage/' . $org->logo_path) }}" alt="{{ $org->name }}" class="h-full w-full object-cover">
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
    <div x-data="{ contactModal: false, selectedOrg: {} }" x-show="contactModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4" x-cloak>
        <div class="bg-white dark:bg-zinc-900 w-full max-w-[400px] rounded-2xl shadow-2xl overflow-hidden p-8">
            <div class="flex justify-between items-start mb-6">
                <h3 class="text-xl font-black dark:text-white" x-text="selectedOrg.name"></h3>
                <button @click="contactModal = false" class="text-gray-400 hover:text-primary">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest">Email Address</label>
                    <p class="text-sm font-medium dark:text-white" x-text="selectedOrg.email || 'N/A'"></p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest">Phone Number</label>
                    <p class="text-sm font-medium dark:text-white" x-text="selectedOrg.phone || 'N/A'"></p>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest">Website</label>
                    <a :href="selectedOrg.website" class="text-sm font-medium text-primary hover:underline" x-text="selectedOrg.website || 'N/A'"></a>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest">Address</label>
                    <p class="text-sm font-medium dark:text-white" x-text="selectedOrg.address || 'N/A'"></p>
                </div>
                <div x-show="selectedOrg.kyc_document_path">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest">KYC Document</label>
                    <a :href="'/storage/' + selectedOrg.kyc_document_path" target="_blank" class="text-xs font-bold text-primary flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-sm">description</span>
                        View Document
                    </a>
                </div>
            </div>
            
            <div class="mt-8">
                <a :href="'mailto:' + selectedOrg.email" class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/20">
                    Send Email
                </a>
            </div>
        </div>
    </div>
</div>

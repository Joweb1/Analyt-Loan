<div>
    @section('title', 'Collateral Vault Inventory')
    <div class="px-0 pt-4">
        <a href="{{ route('collateral.create') }}" class="flex items-center gap-3 px-6 py-3 bg-primary text-white rounded-full shadow-md hover:scale-105 active:scale-95 transition-all w-fit">
            <span class="material-symbols-outlined">add</span>
            <span class="font-bold tracking-tight">Add Collateral</span>
        </a>
    </div>
    
    <section class="px-0 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-900 p-4 rounded-[12px] border border-[#dbdee6] dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-[#606e8a]">Total Vault Value</p>
                <div class="p-1.5 bg-primary/10 text-primary rounded-lg">
                    <span class="material-symbols-outlined text-lg">monetization_on</span>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-xl font-extrabold">₦{{ number_format($totalValue, 2) }}</h3>
            </div>
            <p class="text-[9px] text-[#606e8a] mt-1 font-medium tracking-wide uppercase">Real-time Value</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-[12px] border border-[#dbdee6] dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-[#606e8a]">Items In Vault</p>
                <div class="p-1.5 bg-green-500/10 text-green-500 rounded-lg">
                    <span class="material-symbols-outlined text-lg">lock</span>
                </div>
            </div>
            <h3 class="text-xl font-extrabold">{{ $inVaultCount }}</h3>
            <p class="text-[9px] text-[#606e8a] mt-1 font-medium tracking-wide uppercase">Secured Assets</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 rounded-[12px] border border-[#dbdee6] dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-2">
                <p class="text-xs font-semibold text-[#606e8a]">Returned Items</p>
                <div class="p-1.5 bg-blue-500/10 text-blue-500 rounded-lg">
                    <span class="material-symbols-outlined text-lg">assignment_return</span>
                </div>
            </div>
            <h3 class="text-xl font-extrabold">{{ $returnedCount }}</h3>
            <p class="text-[9px] text-[#606e8a] mt-1 font-medium tracking-wide uppercase">Released to Borrower</p>
        </div>
    </section>

    <section class="px-0 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white dark:bg-gray-900 rounded-[16px] border border-[#dbdee6] dark:border-gray-800">
            <div class="flex gap-2">
                <button wire:click="setFilter('all')" class="flex items-center gap-2 px-4 py-1.5 text-sm font-bold rounded-lg transition-all {{ $filter === 'all' ? 'bg-primary text-white' : 'bg-[#f0f1f5] dark:bg-gray-800 text-[#111318] dark:text-white hover:bg-gray-200' }}">
                    All Assets
                </button>
                <button wire:click="setFilter('in_vault')" class="flex items-center gap-2 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $filter === 'in_vault' ? 'bg-primary text-white' : 'bg-[#f0f1f5] dark:bg-gray-800 text-[#111318] dark:text-white hover:bg-gray-200' }}">
                    In Vault
                    <span class="px-1.5 py-0.5 bg-white dark:bg-gray-700 text-[10px] rounded-md {{ $filter === 'in_vault' ? 'text-primary' : '' }}">{{ $inVaultCount }}</span>
                </button>
                <button wire:click="setFilter('returned')" class="flex items-center gap-2 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $filter === 'returned' ? 'bg-primary text-white' : 'bg-[#f0f1f5] dark:bg-gray-800 text-[#111318] dark:text-white hover:bg-gray-200' }}">
                    Returned
                </button>
            </div>
            
            <div class="relative w-full md:w-96">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search assets, borrowers, loans..." class="w-full pl-10 pr-4 py-2 bg-[#f0f1f5] dark:bg-gray-800 border-none rounded-lg text-sm font-medium focus:ring-2 focus:ring-primary/20 placeholder-gray-500 text-slate-900 dark:text-white">
            </div>
        </div>
    </section>

    <section class="px-0 pb-4">
        <div class="bg-white dark:bg-gray-900 rounded-[16px] border border-[#dbdee6] dark:border-gray-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#f8f9fa] dark:bg-gray-800/50 border-b border-[#dbdee6] dark:border-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Asset Item</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Owner/Borrower</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider text-right">Value</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Condition</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#dbdee6] dark:divide-gray-800">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-[#f8f9fa] dark:hover:bg-gray-800/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="size-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-primary group-hover:scale-105 transition-transform overflow-hidden">
                                            @if($asset->image_path)
                                                <img src="{{ $asset->image_url }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-xl">inventory_2</span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#111318] dark:text-white leading-tight text-sm">{{ $asset->name }}</p>
                                            <p class="text-[10px] text-[#606e8a]">{{ $asset->type }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($asset->loan && $asset->loan->borrower)
                                            <div class="size-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[10px] font-bold">
                                                {{ substr($asset->loan->borrower->user->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold">{{ $asset->loan->borrower->user->name }}</p>
                                                <p class="text-[10px] text-[#606e8a]">Loan #{{ $asset->loan->loan_number }}</p>
                                            </div>
                                        @else
                                            <div class="size-6 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 text-[10px] font-bold">
                                                CO
                                            </div>
                                            <span class="text-sm font-semibold">Company Asset</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-bold">₦{{ number_format($asset->value, 2) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($asset->status === 'in_vault')
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">In Vault</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">Returned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-[#606e8a]">{{ $asset->condition ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="text-[#606e8a] hover:text-primary transition-colors p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    
                                    <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-48 bg-white dark:bg-[#1a1f2b] rounded-xl shadow-xl border border-slate-100 dark:border-slate-800 z-20 overflow-hidden" style="display: none;">
                                        <div class="py-1">
                                            <button wire:click="viewAsset('{{ $asset->id }}'); open = false" class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 transition-colors">
                                                <span class="material-symbols-outlined text-base">visibility</span> View Details
                                            </button>
                                            <a href="{{ route('collateral.create', ['loan_id' => $asset->loan_id]) }}" class="w-full px-4 py-2.5 text-left text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2 transition-colors">
                                                <span class="material-symbols-outlined text-base">edit</span> Edit Asset
                                            </a>
                                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                            <button wire:click="deleteAsset('{{ $asset->id }}')" wire:confirm="Are you sure you want to delete this asset?" class="w-full px-4 py-2.5 text-left text-xs font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 transition-colors">
                                                <span class="material-symbols-outlined text-base">delete</span> Delete
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-[#606e8a]">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inventory_2</span>
                                        <p class="text-sm font-medium">No assets found in the vault.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-[#dbdee6] dark:border-gray-800">
                {{ $assets->links() }}
            </div>
        </div>
    </section>

    <!-- View Asset Modal -->
    <div x-data="{ open: @entangle('showViewModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full h-full sm:h-auto sm:max-w-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            @if($viewingAsset)
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#1a1f2b] sticky top-0 z-10">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white">Asset Details</h3>
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">
                            @if($viewingAsset->loan)
                                Linked to Loan #{{ $viewingAsset->loan->loan_number }}
                            @else
                                Company Property
                            @endif
                        </p>
                    </div>
                    <button @click="open = false" class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-slate-500">close</span>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="aspect-video rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700 relative">
                                @if($viewingAsset->image_path)
                                    <img src="{{ $viewingAsset->image_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined text-6xl opacity-50">image</span>
                                        <span class="text-xs font-bold uppercase mt-2">No Image</span>
                                    </div>
                                @endif
                                <div class="absolute top-4 right-4">
                                    <span class="px-3 py-1 rounded-lg bg-white/90 dark:bg-black/50 backdrop-blur-sm text-xs font-black uppercase tracking-wider {{ $viewingAsset->status === 'in_vault' ? 'text-green-600' : 'text-slate-500' }}">
                                        {{ str_replace('_', ' ', $viewingAsset->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-2xl font-black text-slate-900 dark:text-white">{{ $viewingAsset->name }}</h4>
                                <p class="text-sm text-slate-500 font-medium mt-1">{{ $viewingAsset->description ?? 'No description provided.' }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Asset Value</p>
                                    <p class="text-lg font-black text-slate-900 dark:text-white">₦{{ number_format($viewingAsset->value, 2) }}</p>
                                </div>
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Condition</p>
                                    <p class="text-lg font-black text-slate-900 dark:text-white">{{ $viewingAsset->condition ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Type</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $viewingAsset->type }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Owner</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $viewingAsset->loan ? $viewingAsset->loan->borrower->user->name : 'Company Owned' }}
                                    </span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Registered</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $viewingAsset->registered_date ? $viewingAsset->registered_date->format('M d, Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-[#1a1f2b] flex justify-end gap-3 sticky bottom-0 z-10">
                    <button wire:click="deleteAsset('{{ $viewingAsset->id }}')" wire:confirm="Are you sure you want to delete this asset?" class="px-5 py-2.5 rounded-xl bg-red-50 text-red-600 font-bold text-xs hover:bg-red-100 transition-all">Delete</button>
                    <a href="{{ route('collateral.create', ['loan_id' => $viewingAsset->loan_id]) }}" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">edit</span> Edit Details
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
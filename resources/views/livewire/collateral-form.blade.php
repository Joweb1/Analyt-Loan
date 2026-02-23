<div class="max-w-4xl mx-auto p-4 lg:p-8 space-y-6">
    @if(!$selectedLoan && !$isBranchAsset)
        <!-- SELECTION SCREEN -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Add Collateral</h2>
                <p class="text-sm text-slate-500 font-medium">Link a new asset to a loan or register as company property.</p>
            </div>
            <a href="{{ route('vault') }}" class="px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Back to Vault
            </a>
        </div>

        <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl p-8 shadow-sm border border-slate-100 dark:border-slate-800 space-y-8">
            <!-- Search Section -->
            <div class="space-y-4">
                <label class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Find Loan Profile</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-3.5 text-slate-400">search</span>
                    <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Search by Borrower Name, Loan ID, NIN, Phone or Email..." class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white placeholder-slate-400">
                </div>

                @if(!empty($searchQuery))
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                        @forelse($searchedLoans as $loan)
                            <button wire:click="selectLoan('{{ $loan->id }}')" class="w-full text-left p-4 hover:bg-white dark:hover:bg-slate-800 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors group">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $loan->borrower->user->name }}</p>
                                        <p class="text-xs text-slate-500 font-medium">Loan #{{ $loan->loan_number }} • {{ $loan->borrower->phone }}</p>
                                    </div>
                                    <span class="material-symbols-outlined text-slate-300 group-hover:text-primary">chevron_right</span>
                                </div>
                            </button>
                        @empty
                            <div class="p-4 text-center text-slate-400 text-xs font-medium">
                                No matching loans found.
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            <div class="relative flex py-2 items-center">
                <div class="flex-grow border-t border-slate-100 dark:border-slate-800"></div>
                <span class="flex-shrink-0 mx-4 text-slate-300 text-xs font-bold uppercase">OR</span>
                <div class="flex-grow border-t border-slate-100 dark:border-slate-800"></div>
            </div>

            <!-- Branch Asset Option -->
            <button wire:click="selectBranch" class="w-full p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl flex items-center gap-4 hover:border-primary/50 hover:bg-primary/5 transition-all group">
                <div class="size-12 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-2xl text-slate-500 group-hover:text-white">domain</span>
                </div>
                <div class="text-left">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-primary">Register as Company Asset</h4>
                    <p class="text-xs text-slate-500 font-medium">This asset belongs to the branch/company, not a specific loan.</p>
                </div>
            </button>
        </div>

    @else
        <!-- FORM SCREEN -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $collateral_id ? 'Edit Collateral' : 'Add Collateral' }}
                </h2>
                <p class="text-sm text-slate-500 font-medium">
                    @if($selectedLoan)
                        For Loan #{{ $selectedLoan->loan_number }} ({{ $selectedLoan->borrower->user->name }})
                    @else
                        Registering as Company Asset (Branch Property)
                    @endif
                </p>
            </div>
            <button onclick="history.back()" class="px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Return
            </button>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl p-8 shadow-sm border border-slate-100 dark:border-slate-800">
            <form wire:submit.prevent="save" class="space-y-8">
                <!-- Asset Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Asset Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. Toyota Camry 2020" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white placeholder-slate-400 text-sm">
                        @error('name') <span class="text-[10px] font-bold text-red-500 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Asset Type</label>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white text-sm text-left flex items-center justify-between">
                                <span x-text="$wire.type"></span>
                                <span class="material-symbols-outlined text-lg text-slate-500">unfold_more</span>
                            </button>
                            
                            <div x-show="open" @click.outside="open = false" class="absolute z-20 mt-2 w-full bg-white dark:bg-[#1a1f2b] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden" style="display: none;">
                                <div class="max-h-48 overflow-y-auto py-1 custom-scrollbar">
                                    @foreach(['Vehicle', 'Real Estate', 'Jewelry', 'Electronics', 'Investment', 'Other'] as $option)
                                        <button @click="$wire.set('type', '{{ $option }}'); open = false" type="button" class="w-full px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between transition-colors group">
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $option }}</span>
                                            <span x-show="$wire.type === '{{ $option }}'" class="material-symbols-outlined text-primary text-lg">check</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Market Value (₦)</label>
                        <input wire:model="value" type="number" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white placeholder-slate-400 text-sm">
                        @error('value') <span class="text-[10px] font-bold text-red-500 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Condition</label>
                        <div x-data="{ open: false }" class="relative">
                             <button @click="open = !open" type="button" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white text-sm text-left flex items-center justify-between">
                                <span x-text="$wire.condition"></span>
                                <span class="material-symbols-outlined text-lg text-slate-500">unfold_more</span>
                            </button>

                            <div x-show="open" @click.outside="open = false" class="absolute z-20 mt-2 w-full bg-white dark:bg-[#1a1f2b] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden" style="display: none;">
                                <div class="max-h-48 overflow-y-auto py-1 custom-scrollbar">
                                    @foreach(['New', 'Like New', 'Good', 'Fair', 'Poor'] as $option)
                                        <button @click="$wire.set('condition', '{{ $option }}'); open = false" type="button" class="w-full px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between transition-colors group">
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $option }}</span>
                                            <span x-show="$wire.condition === '{{ $option }}'" class="material-symbols-outlined text-primary text-lg">check</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Registered Date</label>
                        <input wire:model="registered_date" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white text-sm">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Status</label>
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white text-sm text-left flex items-center justify-between">
                                <span x-text="$wire.status === 'in_vault' ? 'In Vault (Secure)' : 'Returned to Borrower'"></span>
                                <span class="material-symbols-outlined text-lg text-slate-500">unfold_more</span>
                            </button>

                            <div x-show="open" @click.outside="open = false" class="absolute z-20 mt-2 w-full bg-white dark:bg-[#1a1f2b] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden" style="display: none;">
                                <div class="py-1">
                                    <button @click="$wire.set('status', 'in_vault'); open = false" type="button" class="w-full px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between transition-colors group">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-green-500">lock</span>
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">In Vault (Secure)</span>
                                        </div>
                                        <span x-show="$wire.status === 'in_vault'" class="material-symbols-outlined text-primary text-lg">check</span>
                                    </button>
                                    <button @click="$wire.set('status', 'returned'); open = false" type="button" class="w-full px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-between transition-colors group">
                                         <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-slate-400">assignment_return</span>
                                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Returned to Borrower</span>
                                        </div>
                                        <span x-show="$wire.status === 'returned'" class="material-symbols-outlined text-primary text-lg">check</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Description / Notes</label>
                        <textarea wire:model="description" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-medium text-slate-900 dark:text-white placeholder-slate-400 text-sm custom-scrollbar"></textarea>
                    </div>

                     <!-- Image Upload -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-slate-500 uppercase tracking-wider px-1">Asset Image</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-2xl cursor-pointer bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors relative overflow-hidden group">
                                
                                @if ($image)
                                    <img src="{{ $image->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white font-bold text-sm">Change Image</span>
                                    </div>
                                @elseif ($current_image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($current_image) }}" class="absolute inset-0 w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white font-bold text-sm">Change Image</span>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <span class="material-symbols-outlined text-4xl text-slate-400 mb-3">add_photo_alternate</span>
                                        <p class="mb-2 text-sm text-slate-500 font-bold">Click to upload</p>
                                        <p class="text-xs text-slate-400">SVG, PNG, JPG or GIF (MAX. 2MB)</p>
                                    </div>
                                @endif
                                <input wire:model="image" id="dropzone-file" type="file" class="hidden" accept="image/*" />
                            </label>
                        </div> 
                        @error('image') <span class="text-[10px] font-bold text-red-500 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="px-8 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined">save</span>
                        Save Collateral
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
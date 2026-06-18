<div id="loan-form-container" data-component-id="{{ fetch_data($this?->getId() ?? null) }}" class="w-full bg-white border-t border-gray-200 pb-64">
    <form wire:submit.prevent="saveLoan" class="space-y-0">
        
        <!-- State Keepers for JS Calculation -->
        <input type="hidden" id="h_interest_calc_type" value="{{ $interest_calculation_type }}">
        <input type="hidden" id="h_interest_cycle" value="{{ $interest_cycle }}">
        <input type="hidden" id="h_insurance_fee" value="{{ $insurance_fee }}">
        <input type="hidden" id="h_insurance_fee_type" value="{{ $insurance_fee_type }}">
        <input type="hidden" id="h_duration_unit" value="{{ $duration_unit }}">
        <input type="hidden" id="h_repayment_cycle" value="{{ $repayment_cycle }}">

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="mx-6 mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                <strong class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">error</span>
                    Oops!
                </strong>
                <ul class="mt-2 list-disc list-inside text-sm opacity-80">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
            
            <!-- Column 1: Borrower & Product -->
            <div class="p-6 md:p-8 space-y-8 border-b lg:border-b-0">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Borrower & Product</h3>
                    <p class="text-sm text-gray-500 mb-6">Who is taking the loan and which product?</p>

                    <!-- Borrower Search -->
                    <div class="space-y-6">
                        <div class="relative group">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Select Borrower</label>
                            @if($selectedBorrower)
                                <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-xl">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ fetch_data($selectedBorrower?->user?->name ?? 'Unknown' ?? null) }}</p>
                                        <p class="text-xs text-blue-700">{{ fetch_data($selectedBorrower?->phone ?? null) }}</p>
                                    </div>
                                    <button type="button" wire:click="resetBorrower" class="p-1 rounded-full text-gray-400 hover:text-red-500 transition-colors">
                                        <span class="material-symbols-outlined">close</span>
                                    </button>
                                </div>
                            @else
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400">search</span>
                                    </span>
                                    <input type="text" wire:model.live="search" 
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm transition-all" 
                                        placeholder="Search Name, Phone, BVN..." autocomplete="off">
                                    
                                    @if(strlen($search) > 1 && count($searchResults) > 0)
                                        <div class="absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                            @foreach($searchResults as $result)
                                                <div wire:click="selectBorrower('{{ fetch_data($result?->id ?? null) }}')" class="cursor-pointer select-none relative py-3 pl-3 pr-9 hover:bg-blue-50 transition-colors">
                                                    <div class="flex items-center">
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-gray-900">{{ fetch_data($result?->name ?? null) }}</p>
                                                            <p class="text-xs text-gray-500">{{ fetch_data($result?->phone ?? null) }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @error('borrowerId') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Loan Product</label>
                            <div class="relative custom-dropdown" id="dd-product">
                                <button type="button" onclick="toggleDropdown('dd-product')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                    <span class="block truncate {{ $loan_product ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                        {{ $loan_product ?: 'Select Product...' }}
                                    </span>
                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                        <span class="material-symbols-outlined text-gray-400">expand_more</span>
                                    </span>
                                </button>
                                <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                    @foreach($loanProducts as $product)
                                        <div onclick="selectOption('loan_product', '{{ fetch_data($product?->name ?? null) }}')" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900 hover:text-blue-900">
                                            <span class="block truncate font-normal">{{ fetch_data($product?->name ?? null) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('loan_product') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Financial Terms (TOGGLEABLE) -->
            <div class="p-6 md:p-8 space-y-8" x-data="{ showAdvanced: false }">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <h3 class="text-lg font-bold text-gray-900">Financial Terms</h3>
                        <button type="button" @click="showAdvanced = !showAdvanced" class="text-primary hover:bg-primary/5 p-1 px-2 rounded-lg transition-colors flex items-center gap-1">
                            <span class="text-[10px] font-black uppercase tracking-widest" x-text="showAdvanced ? 'Hide Terms' : 'View Terms'"></span>
                            @if($termsLocked) <span class="material-symbols-outlined text-[12px] text-amber-500">lock</span> @endif
                            <span class="material-symbols-outlined text-lg transition-transform duration-300" :class="showAdvanced ? 'rotate-180' : ''">expand_more</span>
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mb-6">Principal amount and repayment settings.</p>

                    <div class="space-y-6">
                        <!-- Amount (Always Visible) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Principal Amount</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-bold">₦</span>
                                </div>
                                <input type="number" id="amount" wire:model.live="amount" class="block w-full pl-10 pr-12 py-3 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm font-semibold text-gray-900 bg-gray-50" placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 sm:text-xs">NGN</span>
                                </div>
                            </div>
                            @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Advanced/Locked Terms -->
                        <div x-show="showAdvanced" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6 pt-4 border-t border-gray-100">
                            
                            <!-- Interest Rate -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    Interest Rate @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                </label>
                                <div class="flex rounded-xl shadow-sm relative">
                                    <input type="number" id="interest_rate" wire:model="interest_rate" step="0.01" @disabled($termsLocked) class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-gray-50 focus:ring-blue-700 focus:border-blue-700 sm:text-sm font-semibold disabled:opacity-75 disabled:cursor-not-allowed">
                                    <div class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-gray-100 text-gray-600 font-medium sm:text-sm">
                                        {{ $interest_calculation_type == 'percentage' ? '%' : '₦' }}
                                    </div>
                                </div>
                                @error('interest_rate') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <!-- Interest Cycle -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    Interest Applied @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                </label>
                                <div class="relative custom-dropdown" id="dd-interest-cycle">
                                    <button type="button" @disabled($termsLocked) onclick="toggleDropdown('dd-interest-cycle')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                                        <span class="block truncate text-gray-900 font-medium">Per {{ ucfirst($interest_cycle) }}</span>
                                        @if(!$termsLocked) <span class="material-symbols-outlined text-gray-400">expand_more</span> @endif
                                    </button>
                                    @if(!$termsLocked)
                                        <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                            @foreach(['day', 'week', 'biweekly', 'month', 'year'] as $cycle)
                                                <div onclick="selectOption('interest_cycle', '{{ $cycle }}')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900">Per {{ ucfirst($cycle) }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Duration -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                    Loan Duration @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                </label>
                                <div class="flex rounded-xl shadow-sm relative">
                                    <input type="number" id="duration" wire:model.live="duration" @disabled($termsLocked) class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-gray-50 focus:ring-blue-700 focus:border-blue-700 sm:text-sm font-semibold disabled:opacity-75 disabled:cursor-not-allowed">
                                    <div class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-gray-100 text-gray-600 font-medium sm:text-sm">
                                        {{ ucfirst($duration_unit) }}s
                                    </div>
                                </div>
                                @error('duration') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                             <!-- Repayment Cycle -->
                             <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                        Repay Cycle @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                    </label>
                                    <div class="relative custom-dropdown" id="dd-cycle">
                                        <button type="button" @disabled($termsLocked) onclick="toggleDropdown('dd-cycle')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                                             {{ ucfirst($repayment_cycle) }}
                                        </button>
                                        @if(!$termsLocked)
                                            <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm dd-menu">
                                                @foreach(['daily', 'weekly', 'biweekly', 'monthly', 'yearly'] as $rc)
                                                    <div onclick="selectOption('repayment_cycle', '{{ $rc }}')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900">{{ ucfirst($rc) }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                        Installments @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                    </label>
                                    <input type="number" id="num_repayments" wire:model.live="num_repayments" @disabled($termsLocked) class="block w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50 focus:ring-blue-700 focus:border-blue-700 sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                                    @error('num_repayments') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <!-- Fees -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                        Processing Fee @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                    </label>
                                    <div class="flex rounded-xl shadow-sm relative">
                                        <input type="number" wire:model="processing_fee" @disabled($termsLocked) class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-white focus:ring-blue-700 focus:border-blue-700 sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                                        <div class="inline-flex items-center px-3 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-gray-100 text-gray-600 font-medium sm:text-xs">
                                            {{ $processing_fee_type == 'fixed' ? '₦' : '%' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                        Insurance Fee @if($termsLocked) <span class="material-symbols-outlined text-[10px] text-amber-500">lock</span> @endif
                                    </label>
                                    <div class="flex rounded-xl shadow-sm relative">
                                        <input type="number" wire:model="insurance_fee" @disabled($termsLocked) class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-white focus:ring-blue-700 focus:border-blue-700 sm:text-sm disabled:opacity-75 disabled:cursor-not-allowed">
                                        <div class="inline-flex items-center px-3 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-gray-100 text-gray-600 font-medium sm:text-xs">
                                            {{ $insurance_fee_type == 'fixed' ? '₦' : '%' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Details (TOGGLEABLE) -->
        <div class="mt-8 px-6 md:px-8 pb-12" x-data="{ showAdvancedDetails: false }">
            <div class="bg-gray-50 rounded-2xl border border-gray-200 overflow-visible relative z-10">
                <button type="button" @click="showAdvancedDetails = !showAdvancedDetails" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-100 transition-colors rounded-t-2xl">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">settings</span>
                        <h3 class="text-lg font-bold text-gray-900">Advanced Details</h3>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">(Optional)</span>
                    </div>
                    <span class="material-symbols-outlined transition-transform duration-300" :class="showAdvancedDetails ? 'rotate-180' : ''">expand_more</span>
                </button>

                <div x-show="showAdvancedDetails" x-cloak x-collapse class="p-6 md:p-8 space-y-8 border-t border-gray-200 bg-white overflow-visible rounded-b-2xl relative z-20">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 overflow-visible">
                        <!-- Left Column -->
                        <div class="space-y-6 overflow-visible">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Release Date</label>
                                <input type="date" wire:model="release_date" class="block w-full px-4 py-3 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm bg-gray-50">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Collection Group <span class="text-gray-400">(Optional)</span></label>
                                <div class="relative custom-dropdown" id="dd-group">
                                    <button type="button" onclick="toggleDropdown('dd-group')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                        <span class="block truncate {{ $collection_group ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                            {{ $collection_group ?: 'Select Collection Day...' }}
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <span class="material-symbols-outlined text-gray-400">expand_more</span>
                                        </span>
                                    </button>
                                    <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('collection_group', '')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-500 italic">None</div>
                                        @foreach(['Monday Group', 'Tuesday Group', 'Wednesday Group', 'Thursday Group', 'Friday Group'] as $group)
                                            <div onclick="selectOption('collection_group', '{{ $group }}')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900">
                                                {{ $group }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @error('collection_group') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Loan Officer <span class="text-gray-400">(Optional)</span></label>
                                <div class="relative custom-dropdown" id="dd-officer">
                                    <button type="button" onclick="toggleDropdown('dd-officer')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                        <span class="block truncate {{ $loan_officer_id ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                            {{ $loan_officer_id ? $staffMembers->firstWhere('id', $loan_officer_id)?->name : 'Auto-assign to Me...' }}
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <span class="material-symbols-outlined text-gray-400">expand_more</span>
                                        </span>
                                    </button>
                                    <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('loan_officer_id', '')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-500 italic">Auto-assign to Me</div>
                                        @foreach($staffMembers as $staff)
                                            <div onclick="selectOption('loan_officer_id', '{{ fetch_data($staff?->id ?? null) }}')" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900 hover:text-blue-900">
                                                <span class="block truncate font-normal">{{ fetch_data($staff?->name ?? null) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Portfolio <span class="text-gray-400">(Optional)</span></label>
                                <div class="relative custom-dropdown" id="dd-portfolio">
                                    <button type="button" onclick="toggleDropdown('dd-portfolio')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                        <span class="block truncate {{ $portfolio_id ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                            {{ $portfolio_id ? $portfolios->firstWhere('id', $portfolio_id)?->name : 'Select Portfolio...' }}
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <span class="material-symbols-outlined text-gray-400">expand_more</span>
                                        </span>
                                    </button>
                                    <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('portfolio_id', '')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-500 italic">None</div>
                                        @foreach($portfolios as $portfolio)
                                            <div onclick="selectOption('portfolio_id', '{{ fetch_data($portfolio?->id ?? null) }}')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900">
                                                {{ fetch_data($portfolio?->name ?? null) }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description <span class="text-gray-400">(Optional)</span></label>
                                <textarea wire:model="description" rows="4" class="block w-full px-4 py-3 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm bg-gray-50 resize-none" placeholder="Internal notes or loan purpose..."></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Collateral <span class="text-gray-400">(Optional)</span></label>
                                <div class="relative custom-dropdown" id="dd-collateral">
                                    <button type="button" onclick="toggleDropdown('dd-collateral')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                        <span class="block truncate {{ $collateralId ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                            {{ $collateralId ? $collaterals->firstWhere('id', $collateralId)?->name : 'Select Collateral Asset...' }}
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <span class="material-symbols-outlined text-gray-400">expand_more</span>
                                        </span>
                                    </button>
                                    <div wire:ignore class="hidden absolute z-[9999] mt-1 w-full bg-white shadow-lg max-h-72 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('collateralId', '')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-500 italic">None</div>
                                        @foreach($collaterals as $col)
                                            <div onclick="selectOption('collateralId', '{{ fetch_data($col?->id ?? null) }}')" class="cursor-pointer py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900">
                                                {{ fetch_data($col?->name ?? null) }} (₦{{ fetch_data($col?->value?->format() ?? null) }})
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Loan Documents <span class="text-gray-400">(Optional)</span></label>
                                @if (!$attachments)
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition-colors cursor-pointer relative">
                                        <div class="space-y-1 text-center">
                                            <span class="material-symbols-outlined text-4xl text-gray-400">upload_file</span>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-700 hover:text-blue-500">
                                                    <span>Upload a file</span>
                                                    <input id="file-upload" wire:model="attachments" type="file" class="sr-only">
                                                </label>
                                            </div>
                                            <p class="text-[10px] text-gray-400 uppercase font-black">PDF, JPG, PNG up to 10MB</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-1 p-4 border-2 border-blue-100 bg-blue-50 rounded-xl flex items-center justify-between">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <span class="material-symbols-outlined text-blue-500 shrink-0">description</span>
                                            <span class="text-xs font-bold truncate">{{ $attachments->getClientOriginalName() }}</span>
                                        </div>
                                        <button type="button" wire:click="$set('attachments', null)" class="text-red-500 hover:bg-red-50 p-1 rounded-full transition-colors shrink-0">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                @endif
                                @error('attachments') <p class="text-[10px] font-black text-red-500 mt-1 uppercase">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Guarantor Sub-section -->
                    <div class="mt-8 pt-8 border-t border-gray-100 relative z-30 overflow-visible">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">shield_person</span>
                            Guarantor Information <span class="text-gray-400 font-normal">(Optional)</span>
                        </h4>
                        <div class="relative overflow-visible">
                            <livewire:components.guarantor-select :excludeId="$borrowerUserId" :key="'guarantor-select-'.$borrowerUserId" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Fixed Footer -->
        <div class="fixed bottom-6 left-4 right-4 md:left-8 md:right-8 lg:left-auto lg:right-12 lg:w-[calc(100%-120px)] xl:w-[calc(100%-200px)] z-50">
            <div class="bg-white/90 backdrop-blur-md border border-gray-200 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-4 md:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-col items-center md:items-start space-y-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tracking Number</span>
                    <span class="font-mono font-bold text-primary text-lg">{{ $loan_number ?? 'GENERATING...' }}</span>
                    @error('loan_number') <p class="text-[8px] font-black text-red-500 uppercase">{{ $message }}</p> @enderror
                </div>
                
                <div id="loan-summary-container" wire:ignore class="hidden flex-col items-center md:items-start px-6 border-l border-gray-200">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Est. Installment</span>
                    <span id="loan-summary-text" class="font-bold text-primary text-sm"></span>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveLoan" class="px-12 py-3.5 bg-primary text-white text-sm font-black rounded-xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg flex items-center gap-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveLoan">{{ $isEditMode ? 'UPDATE LOAN' : 'CREATE LOAN' }}</span>
                        <span wire:loading wire:target="saveLoan">PROCESSING...</span>
                        <span class="material-symbols-outlined text-[20px]">send</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function getLoanComponent() {
        const container = document.getElementById('loan-form-container');
        if (!container) return null;
        return typeof Livewire !== 'undefined' ? Livewire.find(container.getAttribute('data-component-id')) : null;
    }

    window.toggleDropdown = function(id) {
        document.querySelectorAll('.custom-dropdown .dd-menu').forEach(el => {
            if (el.parentElement.id !== id) el.classList.add('hidden');
        });
        const dropdown = document.getElementById(id);
        if(dropdown) {
            const menu = dropdown.querySelector('.dd-menu');
            if(menu) menu.classList.toggle('hidden');
        }
    }

    window.selectOption = function(modelName, value) {
        const component = getLoanComponent();
        if(component) component.set(modelName, value);
        document.querySelectorAll('.custom-dropdown .dd-menu').forEach(el => el.classList.add('hidden'));

        if(modelName === 'repayment_cycle') document.getElementById('h_repayment_cycle').value = value;
        if(modelName === 'duration_unit') document.getElementById('h_duration_unit').value = value;
        if(modelName === 'interest_calculation_type') document.getElementById('h_interest_calc_type').value = value;
        if(modelName === 'interest_cycle') document.getElementById('h_interest_cycle').value = value;

        setTimeout(window.calculateLoanDetails, 50);
    }

    window.calculateLoanDetails = function() {
        const amount = parseFloat(document.getElementById('amount')?.value) || 0;
        const interestRate = parseFloat(document.getElementById('interest_rate')?.value) || 0;
        const duration = parseInt(document.getElementById('duration')?.value) || 0;
        
        const numRepaymentsInput = document.getElementById('num_repayments');
        const summaryText = document.getElementById('loan-summary-text');
        const summaryContainer = document.getElementById('loan-summary-container');
        
        const interestCalcType = document.getElementById('h_interest_calc_type')?.value || 'percentage';
        const interestCycle = document.getElementById('h_interest_cycle')?.value || 'month';
        const durationUnit = document.getElementById('h_duration_unit')?.value || 'month';
        const cycle = document.getElementById('h_repayment_cycle')?.value || 'monthly';

        if (duration <= 0 || amount <= 0) {
             if(summaryContainer) summaryContainer.classList.add('hidden');
             return;
        }

        const getDays = (unit) => {
            switch (unit) {
                case 'year': return 240; // 12 months * 20 days
                case 'month': return 20; // 4 weeks * 5 days
                case 'biweekly': return 10; // 2 weeks * 5 days
                case 'week': return 5;
                case 'day': return 1;
                default: return 20;
            }
        };

        const durationInDays = duration * getDays(durationUnit);
        const interestCycleInDays = getDays(interestCycle);
        const ratio = durationInDays / interestCycleInDays;

        let cycleInDays = getDays(cycle === 'weekly' ? 'week' : (cycle === 'biweekly' ? 'biweekly' : (cycle === 'daily' ? 'day' : (cycle === 'yearly' ? 'year' : 'month'))));
        let calcRepayments = Math.ceil(durationInDays / cycleInDays);
        if(calcRepayments < 1) calcRepayments = 1;

        if(numRepaymentsInput && document.activeElement !== numRepaymentsInput) {
            numRepaymentsInput.value = calcRepayments;
            const component = getLoanComponent();
            if(component) component.set('num_repayments', calcRepayments, true);
        } else if (numRepaymentsInput) {
            calcRepayments = parseInt(numRepaymentsInput.value) || calcRepayments;
        }

        let totalInterest = (interestCalcType === 'percentage') ? (amount * (interestRate / 100) * ratio) : (interestRate * ratio);
        const installmentAmount = (amount + totalInterest) / calcRepayments;

        if(summaryText && summaryContainer) {
            const formatted = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(installmentAmount);
            summaryText.innerHTML = `${formatted} / ${cycle}`;
            summaryContainer.classList.remove('hidden');
            summaryContainer.classList.add('flex');
        }
    }

    document.addEventListener('click', e => { if (!e.target.closest('.custom-dropdown')) document.querySelectorAll('.custom-dropdown .dd-menu').forEach(el => el.classList.add('hidden')); });
    ['amount', 'interest_rate', 'duration', 'num_repayments'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('input', window.calculateLoanDetails);
    });
    setTimeout(window.calculateLoanDetails, 500);
</script>
@endpush
@script
<script>
    Livewire.hook('commit', ({ succeed }) => {
        succeed(() => { setTimeout(() => { if (typeof window.calculateLoanDetails === 'function') window.calculateLoanDetails(); }, 50); });
    });
</script>
@endscript

<div id="loan-form-container" data-component-id="{{ $this->getId() }}" class="w-full bg-white border-t border-gray-200 pb-40">
    <form wire:submit.prevent="saveLoan" class="space-y-0">
        
        <!-- State Keepers for JS Calculation -->
        <input type="hidden" id="h_interest_type" value="{{ $interest_type }}">
        <input type="hidden" id="h_duration_unit" value="{{ $duration_unit }}">
        <input type="hidden" id="h_repayment_cycle" value="{{ $repayment_cycle }}">

        <!-- Error Alert -->
        @if ($errors->any())
            <div class="mx-6 mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                <strong class="font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">error</span>
                    Oops!
                </strong>
                <span class="block sm:inline mt-1">Please correct the errors below.</span>
                <ul class="mt-2 list-disc list-inside text-sm opacity-80">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Flash Message (Success) -->
        @if (session()->has('message'))
            <div class="mx-6 mt-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative flex items-center gap-3 shadow-sm" role="alert">
                <span class="material-symbols-outlined text-xl">check_circle</span>
                <div>
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline text-sm">{{ session('message') }}</span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-200">
            
            <!-- Column 1: Borrower & Product -->
            <div class="p-6 md:p-8 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Borrower Info</h3>
                    <p class="text-sm text-gray-500 mb-6">Identify who is taking this loan.</p>

                    <!-- Custom Borrower Search (Combobox) -->
                    <div class="relative group">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Select Borrower</label>
                        
                        @if($selectedBorrower)
                            <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-xl">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $selectedBorrower->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-blue-700">{{ $selectedBorrower->phone }}</p>
                                </div>
                                <button type="button" wire:click="resetBorrower" class="p-1 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        @else
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="text" wire:model.live="search" 
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm transition-all" 
                                    placeholder="Search Name, Phone, BVN..." autocomplete="off">
                                
                                <!-- Dropdown Results -->
                                @if(strlen($search) > 1 && count($searchResults) > 0)
                                    <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        @foreach($searchResults as $result)
                                            <div wire:click="selectBorrower('{{ $result->id }}')" class="cursor-pointer select-none relative py-3 pl-3 pr-9 hover:bg-blue-50 transition-colors">
                                                <div class="flex items-center">
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">{{ $result->user->name ?? 'Unknown' }}</p>
                                                        <p class="text-xs text-gray-500">{{ $result->phone }} • {{ $result->bvn }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(strlen($search) > 1)
                                    <div class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-xl py-4 text-center text-sm text-gray-500 border border-gray-100">
                                        No borrowers found.
                                    </div>
                                @endif
                            </div>
                            @error('borrowerId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        @endif
                    </div>
                </div>

                <div>
                    <!-- Loan Product Custom Dropdown -->
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Loan Product</label>
                    <div class="relative custom-dropdown" id="dd-product">
                        <button type="button" onclick="toggleDropdown('dd-product')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                            <span class="block truncate {{ $loan_product ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                {{ $loan_product ?: 'Select Product...' }}
                            </span>
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </button>
                        <div wire:ignore class="hidden absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                            @foreach(['Personal Loan', 'Business Loan', 'Student Loan', 'Agri Loan'] as $product)
                                <div onclick="selectOption('loan_product', '{{ $product }}')" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900 hover:text-blue-900">
                                    <span class="block truncate font-normal">{{ $product }}</span>
                                    @if($loan_product === $product)
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-700">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('loan_product') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <!-- Collateral Custom Dropdown -->
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Collateral (Optional)</label>
                    <div class="relative custom-dropdown" id="dd-collateral">
                         <button type="button" onclick="toggleDropdown('dd-collateral')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                            <span class="block truncate {{ $collateralId ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                                @if($collateralId)
                                    @php $selectedColl = $collaterals->firstWhere('id', $collateralId); @endphp
                                    {{ $selectedColl ? $selectedColl->name . ' - ₦' . number_format($selectedColl->value) : 'Select Collateral...' }}
                                @else
                                    Select Collateral...
                                @endif
                            </span>
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </button>
                         <div wire:ignore class="hidden absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                            @foreach ($collaterals as $collateral)
                                <div onclick="selectOption('collateralId', '{{ $collateral->id }}')" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900 hover:text-blue-900">
                                    <span class="block truncate font-normal">{{ $collateral->name }} - <span class="text-gray-500">₦{{ number_format($collateral->value) }}</span></span>
                                    @if($collateralId == $collateral->id)
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-700">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('collateralId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Column 2: Terms & Financials -->
            <div class="p-6 md:p-8 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Financial Terms</h3>
                    <p class="text-sm text-gray-500 mb-6">Set the rates, duration, and cycle.</p>

                    <div class="space-y-6">
                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Principal Amount</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-bold">₦</span>
                                </div>
                                <input type="number" id="amount" wire:model="amount" class="block w-full pl-10 pr-12 py-3 border-gray-200 rounded-xl focus:ring-blue-700 focus:border-blue-700 sm:text-sm font-semibold text-gray-900 bg-gray-50" placeholder="0.00">
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 sm:text-xs">NGN</span>
                                </div>
                            </div>
                            @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Interest Rate (Split Input with Custom Mini Dropdown) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Interest Rate</label>
                            <div class="flex rounded-xl shadow-sm relative">
                                <input type="number" id="interest_rate" wire:model="interest_rate" step="0.01" class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-gray-50 focus:ring-blue-700 focus:border-blue-700 sm:text-sm font-semibold" placeholder="Rate">
                                
                                <div class="relative custom-dropdown" id="dd-interest">
                                    <button type="button" onclick="toggleDropdown('dd-interest')" class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-white text-gray-600 font-medium sm:text-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-700">
                                        {{ $interest_type == 'year' ? '% / Yr' : ($interest_type == 'month' ? '% / Mo' : ($interest_type == 'week' ? '% / Wk' : '% / Dy')) }}
                                        <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div wire:ignore class="hidden absolute right-0 z-10 mt-1 w-32 bg-white shadow-lg rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('interest_type', 'year')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Yearly</div>
                                        <div onclick="selectOption('interest_type', 'month')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Monthly</div>
                                        <div onclick="selectOption('interest_type', 'week')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Weekly</div>
                                        <div onclick="selectOption('interest_type', 'day')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Daily</div>
                                    </div>
                                </div>
                            </div>
                            @error('interest_rate') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Duration (Split Input with Custom Mini Dropdown) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Duration</label>
                            <div class="flex rounded-xl shadow-sm relative">
                                <input type="number" id="duration" wire:model.live="duration" class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-gray-50 focus:ring-blue-700 focus:border-blue-700 sm:text-sm font-semibold">
                                
                                <div class="relative custom-dropdown" id="dd-duration">
                                    <button type="button" onclick="toggleDropdown('dd-duration')" class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-white text-gray-600 font-medium sm:text-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-blue-700">
                                        {{ ucfirst($duration_unit) }}s
                                        <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                     <div wire:ignore class="hidden absolute right-0 z-10 mt-1 w-32 bg-white shadow-lg rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('duration_unit', 'year')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Years</div>
                                        <div onclick="selectOption('duration_unit', 'month')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Months</div>
                                        <div onclick="selectOption('duration_unit', 'week')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Weeks</div>
                                        <div onclick="selectOption('duration_unit', 'day')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Days</div>
                                    </div>
                                </div>
                            </div>
                            @error('duration') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                         <!-- Repayment Cycle & Count (Custom Dropdown) -->
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Cycle</label>
                                <div class="relative custom-dropdown" id="dd-cycle">
                                    <button type="button" onclick="toggleDropdown('dd-cycle')" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-4 pr-10 flex items-center justify-between text-left focus:outline-none focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                         <span class="block truncate text-gray-900 font-medium">
                                            {{ ucfirst($repayment_cycle) }}
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </span>
                                    </button>
                                    <div wire:ignore class="hidden absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm dd-menu">
                                        @foreach(['daily', 'weekly', 'biweekly', 'monthly', 'yearly'] as $cycle)
                                            <div onclick="selectOption('repayment_cycle', '{{ $cycle }}')" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-blue-50 text-gray-900">
                                                <span class="block truncate font-normal">{{ ucfirst($cycle) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" id="repayment_cycle_input" value="{{ $repayment_cycle }}">
                                </div>
                                @error('repayment_cycle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Installments</label>
                                <input type="number" id="num_repayments" wire:model="num_repayments" class="block w-full px-4 py-3 border-gray-200 rounded-xl bg-gray-50 focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Column 3: Processing & Meta -->
            <div class="p-6 md:p-8 space-y-8 bg-gray-50/50">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Processing & Docs</h3>
                    <p class="text-sm text-gray-500 mb-6">Fees, description and attachments.</p>

                    <div class="space-y-6">
                        
                         <!-- Release Date -->
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Release Date</label>
                            <input type="date" wire:model="release_date" class="block w-full px-4 py-3 border-gray-200 rounded-xl focus:ring-blue-700 focus:border-blue-700 sm:text-sm bg-white">
                        </div>

                        <!-- Processing Fee (With Custom Mini Dropdown) -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Processing Fee (Optional)</label>
                            <div class="flex rounded-xl shadow-sm relative">
                                <input type="number" wire:model="processing_fee" class="flex-1 min-w-0 block w-full px-4 py-3 rounded-l-xl border-gray-200 bg-white focus:ring-blue-700 focus:border-blue-700 sm:text-sm">
                                
                                <div class="relative custom-dropdown" id="dd-fee-type">
                                    <button type="button" onclick="toggleDropdown('dd-fee-type')" class="inline-flex items-center px-4 py-3 border border-l-0 border-gray-200 rounded-r-xl bg-gray-100 text-gray-600 font-medium sm:text-sm hover:bg-gray-200 focus:outline-none">
                                        {{ $processing_fee_type == 'fixed' ? 'Fixed' : '%' }}
                                        <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                     <div wire:ignore class="hidden absolute right-0 z-10 mt-1 w-24 bg-white shadow-lg rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm dd-menu">
                                        <div onclick="selectOption('processing_fee_type', 'fixed')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">Fixed</div>
                                        <div onclick="selectOption('processing_fee_type', 'percentage')" class="cursor-pointer px-4 py-2 hover:bg-gray-100">%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Insurance Fee -->
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Insurance Fee (Optional)</label>
                             <div class="relative rounded-xl shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-bold">₦</span>
                                </div>
                                <input type="number" wire:model="insurance_fee" class="block w-full pl-10 pr-12 py-3 border-gray-200 rounded-xl focus:ring-blue-700 focus:border-blue-700 sm:text-sm bg-white" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description (Optional)</label>
                            <textarea wire:model="description" rows="3" class="block w-full px-4 py-3 border-gray-200 rounded-xl focus:ring-blue-700 focus:border-blue-700 sm:text-sm bg-white resize-none" placeholder="Optional notes..."></textarea>
                        </div>

                         <!-- File Upload -->
                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Loan Documents (Optional)</label>
                            
                            @if (!$attachments)
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition-colors cursor-pointer relative">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-700 hover:text-blue-500 focus-within:outline-none">
                                                <span>Upload a file</span>
                                                <input id="file-upload" wire:model="attachments" type="file" class="sr-only">
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500">PDF, DOC, IMG up to 10MB</p>
                                    </div>
                                </div>
                            @else
                                <div class="mt-1 p-4 border-2 border-blue-100 bg-blue-50 rounded-xl relative group">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 w-16 h-16 bg-white rounded-lg border border-blue-200 flex items-center justify-center overflow-hidden shadow-sm">
                                            @if (str_starts_with($attachments->getMimeType(), 'image/'))
                                                <img src="{{ $attachments->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-blue-400 text-3xl">
                                                    {{ str_contains($attachments->getMimeType(), 'pdf') ? 'picture_as_pdf' : 'description' }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $attachments->getClientOriginalName() }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($attachments->getSize() / 1024, 1) }} KB</p>
                                        </div>
                                        <button type="button" wire:click="$set('attachments', null)" class="text-gray-400 hover:text-red-500 transition-colors">
                                            <span class="material-symbols-outlined">delete</span>
                                        </button>
                                    </div>
                                    <div wire:loading wire:target="attachments" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center rounded-xl">
                                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                            @error('attachments') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Fixed Footer -->
        <div class="fixed bottom-6 left-4 right-4 md:left-8 md:right-8 lg:left-auto lg:right-12 lg:w-[calc(100%-320px)] xl:w-[calc(100%-400px)] z-50">
            <div class="bg-white/90 backdrop-blur-md border border-gray-200 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] p-4 md:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-col items-center md:items-start space-y-1">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tracking Number</span>
                    <span class="font-mono font-bold text-primary text-lg">{{ $loan_number ?? 'GENERATING...' }}</span>
                </div>
                
                <!-- Loan Summary (Appears when calculated) -->
                <div id="loan-summary-container" wire:ignore class="hidden flex-col items-center md:items-start px-6 border-l border-gray-200">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Repayment Schedule</span>
                    <span id="loan-summary-text" class="font-bold text-primary text-sm"></span>
                </div>

                <div class="flex items-center gap-4 w-full md:w-auto">
                    <button type="submit" wire:ignore wire:loading.attr="disabled" wire:target="saveLoan" class="w-full md:w-auto px-12 py-3.5 bg-primary text-white text-sm font-black rounded-xl hover:opacity-90 hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveLoan" class="flex items-center gap-2">
                            <span>CREATE LOAN RECORD</span>
                            <span class="material-symbols-outlined text-[20px]">send</span>
                        </span>
                        <span wire:loading wire:target="saveLoan" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>PROCESSING...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
    <div class="h-48 md:h-56"></div> <!-- Large spacer to allow scrolling past floating footer -->
</div>

@push('scripts')
<script>
    // Component Instance Helper
    function getLoanComponent() {
        const container = document.getElementById('loan-form-container');
        if (!container) return null;
        if (typeof Livewire !== 'undefined') {
            return Livewire.find(container.getAttribute('data-component-id'));
        }
        return null;
    }

    // Global Functions
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
        if(component) {
            component.set(modelName, value);
        }
        document.querySelectorAll('.custom-dropdown .dd-menu').forEach(el => el.classList.add('hidden'));

        // Update hidden inputs for local calc
        if(modelName === 'repayment_cycle') document.getElementById('h_repayment_cycle').value = value;
        if(modelName === 'duration_unit') document.getElementById('h_duration_unit').value = value;
        if(modelName === 'interest_type') document.getElementById('h_interest_type').value = value;

        setTimeout(window.calculateLoanDetails, 50);
    }

    window.calculateLoanDetails = function() {
        const amount = parseFloat(document.getElementById('amount')?.value) || 0;
        const interestRate = parseFloat(document.getElementById('interest_rate')?.value) || 0;
        const duration = parseInt(document.getElementById('duration')?.value) || 0;
        const numRepaymentsInput = document.getElementById('num_repayments');
        const summaryText = document.getElementById('loan-summary-text');
        const summaryContainer = document.getElementById('loan-summary-container');
        
        const interestType = document.getElementById('h_interest_type')?.value || 'year';
        const durationUnit = document.getElementById('h_duration_unit')?.value || 'month';
        const cycle = document.getElementById('h_repayment_cycle')?.value || 'monthly';

        // Toggle Visibility Logic
        if (duration <= 0 || amount <= 0) {
             if(summaryContainer) {
                 summaryContainer.classList.add('hidden');
                 summaryContainer.classList.remove('flex');
             }
             return;
        }

        // Normalize Duration to Days
        let durationInDays = 0;
        switch (durationUnit) {
            case 'year': durationInDays = duration * 365; break;
            case 'month': durationInDays = duration * 30; break;
            case 'week': durationInDays = duration * 7; break;
            case 'day': durationInDays = duration; break;
        }

        // Determine Cycle Days
        let cycleInDays = 30;
        switch (cycle) {
            case 'daily': cycleInDays = 1; break;
            case 'weekly': cycleInDays = 7; break;
            case 'biweekly': cycleInDays = 14; break;
            case 'monthly': cycleInDays = 30; break;
            case 'yearly': cycleInDays = 365; break;
        }

        let calcRepayments = 0;

        // Specific overrides for clean standard periods
        if (durationUnit === 'year' && cycle === 'monthly') calcRepayments = duration * 12;
        else if (durationUnit === 'year' && cycle === 'weekly') calcRepayments = duration * 52;
        else if (durationUnit === 'year' && cycle === 'biweekly') calcRepayments = duration * 26;
        else if (durationUnit === 'month' && cycle === 'weekly') calcRepayments = duration * 4;
        else if (durationUnit === 'month' && cycle === 'biweekly') calcRepayments = duration * 2;
        else {
            // Fallback to day-based math
            calcRepayments = Math.ceil(durationInDays / cycleInDays);
        }
        if(calcRepayments < 1) calcRepayments = 1;
        
        // Auto-update installments if not focused
        if(numRepaymentsInput && document.activeElement !== numRepaymentsInput) {
            numRepaymentsInput.value = calcRepayments;
            const component = getLoanComponent();
            if(component) component.set('num_repayments', calcRepayments, true);
        } else if (numRepaymentsInput) {
            calcRepayments = parseInt(numRepaymentsInput.value) || calcRepayments;
        }

        // Calculate Interest
        let ratePeriodInDays = 365;
        switch(interestType) {
            case 'year': ratePeriodInDays = 365; break;
            case 'month': ratePeriodInDays = 30; break;
            case 'week': ratePeriodInDays = 7; break;
            case 'day': ratePeriodInDays = 1; break;
        }
        
        const effectiveRate = interestRate * (durationInDays / ratePeriodInDays);
        const totalInterest = amount * (effectiveRate / 100);
        const totalDue = amount + totalInterest;
        const installmentAmount = totalDue / calcRepayments;

        // Update Summary and Show
        if(summaryText && summaryContainer) {
            const formattedInstallment = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(installmentAmount);
            summaryText.innerHTML = `Pays <span class="text-blue-700">${formattedInstallment}</span> ${cycle} for ${duration} ${durationUnit}(s)`;
            summaryContainer.classList.remove('hidden');
            summaryContainer.classList.add('flex');
        }
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('.custom-dropdown')) {
            document.querySelectorAll('.custom-dropdown .dd-menu').forEach(el => el.classList.add('hidden'));
        }
    });

    // Attach listeners
    ['amount', 'interest_rate', 'duration', 'num_repayments'].forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.addEventListener('input', window.calculateLoanDetails);
            el.addEventListener('change', window.calculateLoanDetails);
        }
    });

    // Init
    setTimeout(window.calculateLoanDetails, 500);
</script>
@endpush

<!-- Livewire Script Block for Hydration/Calc -->
@script
<script>
    // This script runs when Livewire initializes/updates.
    
    Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
        succeed(({ snapshot, effect }) => {
            // Re-run calc after updates to restore summary visibility if valid
             setTimeout(() => {
                 if (typeof window.calculateLoanDetails === 'function') {
                     window.calculateLoanDetails();
                 }
             }, 50);
        })
    });

    const durationInput = document.getElementById('duration');
    if(durationInput) {
        durationInput.addEventListener('input', () => {
             // Listener already attached globally
        });
    }
</script>
@endscript
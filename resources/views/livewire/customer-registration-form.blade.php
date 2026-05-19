@php
    $typeLabels = [
        'borrower' => 'Borrower',
        'saver' => 'Saver',
        'guarantor' => 'Guarantor',
    ];
    $currentLabel = $typeLabels[$registration_type] ?? 'Borrower';
@endphp

<div class="relative">
    {{-- Fixed Back Button --}}
    <button onclick="window.history.back()" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-slate-200 dark:border-white/20 rounded-full text-slate-900 dark:text-white hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </button>

    <!-- Dynamic Breadcrumbs -->
    <div class="flex items-center gap-2 mb-6">
        <a class="text-sm font-semibold text-zinc-400 hover:text-primary transition-colors" href="{{ route('dashboard') }}">Dashboard</a>
        <span class="material-symbols-outlined text-zinc-300 text-sm">chevron_right</span>
        <a class="text-sm font-semibold text-zinc-400 hover:text-primary transition-colors" href="{{ route('customer') }}">Customers</a>
        <span class="material-symbols-outlined text-zinc-300 text-sm">chevron_right</span>
        <span class="text-sm font-bold text-primary dark:text-white">{{ $currentLabel }} Registration</span>
    </div>

    <!-- Dynamic Page Heading -->
    <div class="mb-10">
        <h2 class="text-3xl font-black text-primary dark:text-white tracking-tight">Onboard New {{ $currentLabel }}</h2>
        <p class="text-zinc-500 mt-2 font-medium">Complete the form below to register a new {{ strtolower($currentLabel) }} for your organization.</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-[2rem] shadow-xl shadow-primary/5 border border-zinc-100 dark:border-zinc-800 overflow-hidden">
        <div class="p-10">
            <form class="space-y-8" wire:submit.prevent="save">
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl relative" role="alert">
                        <strong class="font-bold">Form Errors</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Registration Type Selection --}}
                <div class="bg-zinc-50 dark:bg-zinc-800/30 p-6 rounded-3xl border border-zinc-100 dark:border-zinc-800 mb-8">
                    <h3 class="text-lg font-bold text-primary dark:text-white mb-6 tracking-tight">Choose Account Type</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="relative flex items-center p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $registration_type === 'borrower' ? 'border-primary bg-primary/5' : 'border-zinc-100 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <input type="radio" wire:model.live="registration_type" value="borrower" class="sr-only">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-sm {{ $registration_type === 'borrower' ? 'text-primary' : 'text-zinc-600 dark:text-zinc-300' }}">Borrower</p>
                                    <p class="text-[10px] text-zinc-400">Needs Loans</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $registration_type === 'saver' ? 'border-primary bg-primary/5' : 'border-zinc-100 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <input type="radio" wire:model.live="registration_type" value="saver" class="sr-only">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-sm {{ $registration_type === 'saver' ? 'text-green-600' : 'text-zinc-600 dark:text-zinc-300' }}">Saver</p>
                                    <p class="text-[10px] text-zinc-400">Save & Earn</p>
                                </div>
                            </div>
                        </label>

                        <label class="relative flex items-center p-4 cursor-pointer rounded-2xl border-2 transition-all {{ $registration_type === 'guarantor' ? 'border-primary bg-primary/5' : 'border-zinc-100 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-700/50' }}">
                            <input type="radio" wire:model.live="registration_type" value="guarantor" class="sr-only">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-sm {{ $registration_type === 'guarantor' ? 'text-blue-600' : 'text-zinc-600 dark:text-zinc-300' }}">Guarantor</p>
                                    <p class="text-[10px] text-zinc-400">Backs Loans</p>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('registration_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Organization Selection (if not pre-set) --}}
                @if(!Auth::check() || !Auth::user()->organization_id)
                    <div class="bg-blue-50 dark:bg-zinc-800 p-6 rounded-2xl border border-blue-100 dark:border-zinc-700 mb-8">
                        <h3 class="text-lg font-bold text-primary dark:text-white mb-4">Select Organization</h3>
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Organization</label>
                            <select wire:model.live="organization_id" class="w-full px-5 py-4 bg-white dark:bg-zinc-900 border-2 border-zinc-100 dark:border-zinc-700 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium">
                                <option value="">Select an Organization</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                @if($registration_type === 'borrower')
                    <div class="bg-indigo-50 dark:bg-zinc-800 p-6 rounded-2xl border border-indigo-100 dark:border-zinc-700 mb-8">
                        <h3 class="text-lg font-bold text-indigo-900 dark:text-white mb-4">Ledger Assignment</h3>
                        <div class="flex flex-col gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Collection Group (Optional)</label>
                                <select wire:model="collection_group" class="w-full px-5 py-4 bg-white dark:bg-zinc-900 border-2 border-zinc-100 dark:border-zinc-700 rounded-2xl focus:border-indigo-500 focus:ring-0 transition-all font-medium">
                                    <option value="">No Group Assigned</option>
                                    <option value="Monday Group">Monday Group</option>
                                    <option value="Tuesday Group">Tuesday Group</option>
                                    <option value="Wednesday Group">Wednesday Group</option>
                                    <option value="Thursday Group">Thursday Group</option>
                                    <option value="Friday Group">Friday Group</option>
                                    <option value="Saturday Group">Saturday Group</option>
                                </select>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase px-1 mt-1">Assigning a group enables easier tracking in the Collection Ledger.</p>
                            </div>

                            <div class="flex flex-col gap-4 p-4 bg-white dark:bg-zinc-900 rounded-2xl border-2 border-indigo-50 dark:border-zinc-800">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_daily_saver" class="w-6 h-6 rounded-lg border-2 border-indigo-100 text-indigo-600 focus:ring-indigo-500 transition-all">
                                    <span class="text-xs font-black text-indigo-900 dark:text-white uppercase tracking-widest">Enroll in Daily Savings</span>
                                </label>
                                @if($is_daily_saver)
                                    <div class="flex flex-col gap-2 animate-in fade-in slide-in-from-top-2 duration-300">
                                        <label class="text-[10px] font-black text-zinc-500 uppercase tracking-widest px-1">Daily Target Amount (₦)</label>
                                        <input wire:model="daily_target_amount" type="number" class="w-full px-5 py-4 bg-indigo-50/50 dark:bg-zinc-800 border-2 border-indigo-100 dark:border-zinc-700 rounded-2xl focus:border-indigo-500 focus:ring-0 transition-all font-black text-indigo-900">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($registration_type === 'saver')
                    <div class="bg-indigo-50 dark:bg-zinc-800 p-6 rounded-2xl border border-indigo-100 dark:border-zinc-700 mb-8">
                        <h3 class="text-lg font-bold text-indigo-900 dark:text-white mb-4">Daily Savings Enrollment</h3>
                        <div class="flex flex-col gap-4 p-4 bg-white dark:bg-zinc-900 rounded-2xl border-2 border-indigo-50 dark:border-zinc-800">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="is_daily_saver" class="w-6 h-6 rounded-lg border-2 border-indigo-100 text-indigo-600 focus:ring-indigo-500 transition-all">
                                <span class="text-xs font-black text-indigo-900 dark:text-white uppercase tracking-widest">Enroll in Daily Savings</span>
                            </label>
                            @if($is_daily_saver)
                                <div class="flex flex-col gap-2 animate-in fade-in slide-in-from-top-2 duration-300">
                                    <label class="text-[10px] font-black text-zinc-500 uppercase tracking-widest px-1">Daily Target Amount (₦)</label>
                                    <input wire:model="daily_target_amount" type="number" class="w-full px-5 py-4 bg-indigo-50/50 dark:bg-zinc-800 border-2 border-indigo-100 dark:border-zinc-700 rounded-2xl focus:border-indigo-500 focus:ring-0 transition-all font-black text-indigo-900">
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- DYNAMIC FORM SECTIONS --}}
                @foreach($configs as $sectionKey => $fields)
                    <div wire:key="section-{{ $sectionKey }}" class="space-y-8 pt-8 border-t border-zinc-100 dark:border-zinc-800 first:border-t-0 first:pt-0">
                        <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">
                            {{ ucfirst(str_replace('_', ' ', $sectionKey)) }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            @foreach($fields as $field)
                                @if($field['is_active'])
                                    @php
                                        $modelName = $field['is_system'] ? $field['name'] : 'customData.' . $field['name'];
                                        $label = $field['label'];
                                        $type = $field['type'];
                                        $isRequired = $field['is_required'];
                                    @endphp

                                    <div wire:key="field-{{ $field['id'] }}" class="flex flex-col gap-2 {{ in_array($type, ['textarea', 'address']) ? 'md:col-span-2' : '' }}">
                                        <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">
                                            {{ $label }} @if($isRequired)<span class="text-red-500">*</span>@endif
                                        </label>

                                        @if($type === 'textarea')
                                            <textarea id="{{ $modelName }}" name="{{ $modelName }}" wire:model="{{ $modelName }}" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium resize-none" rows="3"></textarea>
                                        @elseif($type === 'select')
                                            @if($field['name'] === 'guarantor_id')
                                                <livewire:components.guarantor-select />
                                            @else
                                                <select id="{{ $modelName }}" name="{{ $modelName }}" wire:model="{{ $modelName }}" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium">
                                                    <option value="">Select {{ $label }}</option>
                                                    @if(isset($field['options']) && is_array($field['options']))
                                                        @foreach($field['options'] as $opt)
                                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            @endif
                                        @elseif($type === 'file')
                                            <div class="relative">
                                                <input id="{{ $modelName }}" name="{{ $modelName }}" wire:key="{{ $modelName }}_input" wire:model="{{ $modelName }}" type="file" class="w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary dark:file:bg-primary/80 dark:file:text-white hover:file:bg-primary/20"/>
                                                <div wire:loading wire:target="{{ $modelName }}" class="absolute right-0 top-2 text-xs text-primary animate-pulse">Uploading...</div>
                                            </div>
                                        @else
                                            <input id="{{ $modelName }}" name="{{ $modelName }}" wire:model="{{ $modelName }}" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="{{ $type }}" />
                                        @endif
                                        
                                        <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">
                                            {{ $isRequired ? 'Required' : 'Optional' }} 
                                            @if(in_array($field['name'], ['bvn', 'nin'])) | 11 digits @endif
                                            @if($type === 'email') | Valid email format @endif
                                        </p>
                                        @error($modelName) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Account Password Section --}}
                <div class="space-y-8 pt-8 border-t border-zinc-100 dark:border-zinc-800">
                    <h3 class="text-lg font-bold text-primary dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-2">Set Account Password</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Password -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">
                                Password @if($registration_type === 'borrower')<span class="text-red-500">*</span>@endif
                            </label>
                            <input wire:model="password" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="password" placeholder="••••••••"/>
                            <p class="text-[10px] text-zinc-400 font-bold uppercase px-1">
                                {{ $registration_type === 'borrower' ? 'Required, Min 8 characters' : 'Optional (System default will be used)' }}
                            </p>
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <!-- Confirm Password -->
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-1">Confirm Password</label>
                            <input wire:model="password_confirmation" class="w-full px-5 py-4 bg-zinc-50 dark:bg-zinc-800/50 border-2 border-zinc-100 dark:border-zinc-800 rounded-2xl focus:border-primary focus:ring-0 transition-all font-medium" type="password" placeholder="••••••••"/>
                            @error('password_confirmation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Footer Actions -->
                <div class="pt-10 flex flex-col md:flex-row items-center gap-6 border-t border-zinc-100 dark:border-zinc-800">
                    <button class="w-full md:w-auto min-w-[240px] py-4 bg-primary text-white text-base font-bold rounded-full shadow-xl shadow-primary/30 hover:bg-zinc-800 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            Complete {{ $currentLabel }} Registration
                        </span>
                        <span wire:loading>
                            <span class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Registering...
                            </span>
                        </span>
                    </button>
                    <button onclick="window.history.back()" class="text-zinc-400 hover:text-zinc-600 font-bold text-sm transition-colors uppercase tracking-widest" type="button">
                        Cancel &amp; Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

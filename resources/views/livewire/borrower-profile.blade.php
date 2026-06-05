<div class="w-full mx-auto space-y-8 p-0 relative">
    {{-- Fixed Back Button --}}
    <button onclick="window.history.back()" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-slate-200 dark:border-white/20 rounded-full text-slate-900 dark:text-white hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </button>

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('customer') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Borrower Profile</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Customer Information
                @if($borrower->custom_id)
                    <span class="ml-2 text-sm text-slate-500 font-mono bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md">{{ fetch_data($borrower?->custom_id ?? null) }}</span>
                @endif
            </h2>
        </div>
        <div class="flex gap-3">
             <button wire:click="toggleEdit" class="flex items-center gap-2 px-4 py-2 bg-surface border border-border-main text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                <span class="material-symbols-outlined text-lg">{{ $isEditing ? 'close' : 'edit' }}</span>
                {{ $isEditing ? 'Cancel Edit' : 'Edit Profile' }}
            </button>
            @if($isEditing)
                <button wire:click="save" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Save Changes
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 px-1 lg:px-2 pb-8">
        <!-- Left Column: Photo & Basic Info -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden p-8 text-center">
                <div class="relative inline-block mb-6">
                    <div class="size-32 rounded-full border-4 border-slate-50 dark:border-slate-800 shadow-xl overflow-hidden mx-auto">
                        @if($new_photo)
                            <img src="{{ fetch_data($new_photo?->temporaryUrl() ?? null) }}" class="size-full object-cover">
                        @elseif($photo_url)
                            <img src="{{ $photo_url }}" class="size-full object-cover">
                        @else
                            <div class="size-full bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-5xl">person</span>
                            </div>
                        @endif
                    </div>
                    @if($isEditing)
                        <label class="absolute bottom-0 right-0 size-10 rounded-full bg-primary text-white flex items-center justify-center cursor-pointer shadow-lg hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined text-xl">photo_camera</span>
                            <input type="file" wire:model="new_photo" class="hidden" accept="image/*">
                        </label>
                    @endif
                </div>

                <div class="space-y-1">
                    @if($isEditing)
                        <input wire:model="name" type="text" class="w-full text-center px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-lg font-black focus:ring-2 focus:ring-primary/20">
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Full Name (Required, Max 255)</p>
                        @error('name') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @else
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $name }}</h3>
                    @endif
                    <p class="text-sm text-slate-500 font-medium uppercase tracking-widest">{{ fetch_data($borrower?->user?->getRoleNames()?->first() ?? 'Borrower' ?? null) }}</p>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800/50 space-y-4">
                    <div class="flex flex-col text-left">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</span>
                            @if($isEditing)
                                <input wire:model="email" type="email" class="w-2/3 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-bold focus:ring-2 focus:ring-primary/20">
                            @else
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $email }}</span>
                            @endif
                        </div>
                        @if($isEditing)
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1 text-right">Valid Email (Required, Unique)</p>
                            @error('email') <span class="text-[10px] font-bold text-red-500 mt-1 block text-right">{{ $message }}</span> @enderror
                        @endif
                    </div>
                    <div class="flex flex-col text-left">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1">
                                Phone <span class="material-symbols-outlined text-[12px]">lock</span>
                            </span>
                            @if($isEditing)
                                <input wire:model="phone" type="text" readonly class="w-2/3 px-3 py-1.5 bg-slate-50 dark:bg-zinc-800/50 border-none rounded-lg text-xs font-bold text-slate-400 cursor-not-allowed">
                            @else
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $phone }}</span>
                            @endif
                        </div>
                        @if($isEditing)
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1 text-right italic">Phone cannot be modified</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- KYC Quick Stats -->
            <div class="bg-gradient-to-br from-primary to-blue-700 rounded-2xl p-6 text-white shadow-xl shadow-primary/20">
                <h4 class="text-sm font-black uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">verified_user</span>
                    Trust Verification
                </h4>
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between text-[10px] font-black uppercase mb-1.5">
                            <span>KYC Completion</span>
                            <span>85%</span>
                        </div>
                        <div class="w-full h-1.5 bg-white/20 rounded-full">
                            <div class="bg-white h-full rounded-full w-[85%]"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <p class="text-[9px] font-bold uppercase opacity-60">Repayment Score</p>
                            <p class="text-lg font-black">{{ fetch_data($borrower?->trust_score ?? 0 ?? null) }}%</p>
                        </div>
                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <p class="text-[9px] font-bold uppercase opacity-60">Trust Level</p>
                            <p class="text-lg font-black">
                                @php
                                    $grade = match(true) {
                                        $borrower->trust_score >= 80 => 'Grade A',
                                        $borrower->trust_score >= 50 => 'Grade B',
                                        default => 'Grade C'
                                    };
                                @endphp
                                {{ $grade }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KYC Decision (Only if pending) -->
            @if($kyc_status === 'pending')
                <div class="bg-surface rounded-2xl border border-yellow-200 dark:border-yellow-900/30 shadow-sm p-6 space-y-4">
                    <h4 class="text-sm font-black uppercase tracking-widest text-yellow-600 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">pending_actions</span>
                        KYC DECISION
                    </h4>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Please review the documents carefully before making a decision.</p>
                    
                    <div class="flex flex-col gap-3 pt-2">
                        <button wire:click="approveKyc" class="w-full flex items-center justify-center gap-2 py-3 bg-green-600 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-green-700 transition-all shadow-lg shadow-green-600/20">
                            <span class="material-symbols-outlined text-sm">check_circle</span>
                            Approve Verification
                        </button>
                        
                        <div x-data="{ confirming: false }">
                            <button @click="confirming = true" x-show="!confirming" class="w-full flex items-center justify-center gap-2 py-3 bg-white dark:bg-slate-800 border border-red-200 dark:border-red-900/30 text-red-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-red-50 dark:hover:bg-red-900/10 transition-all">
                                <span class="material-symbols-outlined text-sm">cancel</span>
                                Decline Submission
                            </button>
                            
                            <div x-show="confirming" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-300">
                                <textarea wire:model="rejection_reason" rows="3" placeholder="State reason for rejection..." class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-medium focus:ring-2 focus:ring-red-500/20"></textarea>
                                @error('rejection_reason') <span class="text-[10px] font-bold text-red-500 block">{{ $message }}</span> @enderror
                                <div class="flex gap-2">
                                    <button wire:click="declineKyc" class="flex-1 py-2.5 bg-red-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest">Confirm Decline</button>
                                    <button @click="confirming = false" class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-lg text-[10px] font-black uppercase tracking-widest">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($kyc_status === 'rejected')
                <div class="bg-red-50 dark:bg-red-900/10 rounded-2xl border border-red-100 dark:border-red-900/20 p-6">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-red-600 mb-2">Rejection History</h4>
                    <p class="text-xs text-red-700 dark:text-red-400 font-medium leading-relaxed italic">"{{ fetch_data($borrower?->rejection_reason ?? null) }}"</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Detailed Info & KYC -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-main">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">fingerprint</span>
                        Identification & KYC Data
                    </h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Bank Verification Number (BVN)</label>
                        @if($isEditing)
                            <input wire:model="bvn" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">11 Digits (Optional)</p>
                            @error('bvn') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white font-mono">{{ $bvn ?? 'NOT PROVIDED' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">National Identity Number (NIN)</label>
                        @if($isEditing)
                            <input wire:model="national_identity_number" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">11 Digits (Optional)</p>
                            @error('national_identity_number') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white font-mono">{{ $national_identity_number ?? 'NOT PROVIDED' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Gender</label>
                        @if($isEditing)
                            <select wire:model="gender" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Required</p>
                            @error('gender') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $gender ?? 'N/A' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Date of Birth</label>
                        @if($isEditing)
                            <input wire:model="date_of_birth" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Required</p>
                            @error('date_of_birth') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white">{{ fetch_data($date_of_birth ? \Carbon\Carbon::parse($date_of_birth)?->format('M d, Y') : 'N/A' ?? null) }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Marital Status</label>
                        @if($isEditing)
                            <select wire:model="marital_status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                                <option value="">Select Status</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                                <option value="divorced">Divorced</option>
                                <option value="widowed">Widowed</option>
                            </select>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Required</p>
                            @error('marital_status') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $marital_status ?? 'N/A' }}</p>
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Residential Address</label>
                        @if($isEditing)
                            <textarea wire:model="address" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20"></textarea>
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Full address required</p>
                            @error('address') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $address ?? 'NO ADDRESS ON FILE' }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Collection Group</label>
                        @if($isEditing)
                            <select wire:model="collection_group" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-black focus:ring-2 focus:ring-primary/20">
                                <option value="">None</option>
                                <option value="Monday Group">Monday Group</option>
                                <option value="Tuesday Group">Tuesday Group</option>
                                <option value="Wednesday Group">Wednesday Group</option>
                                <option value="Thursday Group">Thursday Group</option>
                                <option value="Friday Group">Friday Group</option>
                                <option value="Saturday Group">Saturday Group</option>
                            </select>
                        @else
                            <p class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $collection_group ?? 'NOT ASSIGNED' }}</p>
                        @endif
                    </div>

                    {{-- Daily Savings Config --}}
                    <div class="p-4 bg-indigo-50/50 dark:bg-indigo-900/10 rounded-2xl border border-indigo-100 dark:border-indigo-900/30">
                        <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-3">Daily Savings Status</label>
                        @if($isEditing)
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model.live="is_daily_saver" class="w-5 h-5 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest">Active Daily Saver</span>
                                </label>
                                @if($is_daily_saver)
                                    <div>
                                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Daily Target (₦)</label>
                                        <input wire:model="daily_target_amount" type="number" class="w-full px-3 py-2 bg-white dark:bg-slate-800 border-none rounded-lg text-sm font-black focus:ring-2 focus:ring-indigo-500/20">
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm {{ $is_daily_saver ? 'text-emerald-500' : 'text-slate-300' }}">
                                    {{ $is_daily_saver ? 'check_circle' : 'cancel' }}
                                </span>
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest">
                                    {{ $is_daily_saver ? 'Enrolled - ₦' . number_format($daily_target_amount, 2) : 'Not Enrolled' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-main">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">account_balance</span>
                        Disbursement Bank Details
                    </h3>
                </div>
                <div class="p-8">
                    @php $bank_details = $borrower->bank_account_details; @endphp
                    @if(is_array($bank_details))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Bank Name</label>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $bank_details['bank_name'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Account Number</label>
                                <p class="text-sm font-black text-slate-900 dark:text-white font-mono tracking-widest">{{ $bank_details['account_number'] ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Account Name</label>
                                <p class="text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $bank_details['account_name'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300 leading-relaxed">{{ $bank_details ?? 'NO BANK DATA RECORDED' }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-main">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">folder_open</span>
                        Uploaded Documents
                    </h3>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Passport Photograph --}}
                        <div class="flex flex-col gap-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Passport Photograph</span>
                            @if($borrower->passport_photograph)
                                <a href="{{ fetch_data($borrower?->passport_photograph_url ?? null) }}" target="_blank" class="group relative aspect-square rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden border-2 border-slate-50 dark:border-slate-800 hover:border-primary transition-all">
                                    <img src="{{ fetch_data($borrower?->passport_photograph_url ?? null) }}" class="size-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-[10px] font-black uppercase tracking-widest">View Full</span>
                                    </div>
                                </a>
                            @else
                                <div class="aspect-square rounded-xl bg-slate-50 dark:bg-zinc-800/50 flex flex-col items-center justify-center text-slate-300 border-2 border-dashed border-border-main">
                                    <span class="material-symbols-outlined text-3xl">no_photography</span>
                                    <span class="text-[9px] font-bold uppercase mt-2">Not Uploaded</span>
                                </div>
                            @endif
                        </div>

                        {{-- Identity Document --}}
                        <div class="flex flex-col gap-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Identity Document</span>
                            @if($borrower->identity_document)
                                <a href="{{ fetch_data($borrower?->identity_document_url ?? null) }}" target="_blank" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-transparent hover:border-primary transition-all">
                                    <div class="size-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                        <span class="material-symbols-outlined text-2xl">badge</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">ID Document</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Click to view</p>
                                    </div>
                                </a>
                            @else
                                <div class="flex items-center gap-4 p-4 bg-slate-50/50 dark:bg-zinc-800/30 rounded-2xl border-2 border-dashed border-border-main opacity-50">
                                    <div class="size-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                        <span class="material-symbols-outlined text-2xl">error</span>
                                    </div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Missing</p>
                                </div>
                            @endif
                        </div>

                        {{-- Bank Statement --}}
                        <div class="flex flex-col gap-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Bank Statement</span>
                            @if($borrower->bank_statement)
                                <a href="{{ fetch_data($borrower?->bank_statement_url ?? null) }}" target="_blank" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-transparent hover:border-primary transition-all">
                                    <div class="size-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                                        <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Statement</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Click to view</p>
                                    </div>
                                </a>
                            @else
                                <div class="flex items-center gap-4 p-4 bg-slate-50/50 dark:bg-zinc-800/30 rounded-2xl border-2 border-dashed border-border-main opacity-50">
                                    <div class="size-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                        <span class="material-symbols-outlined text-2xl">error</span>
                                    </div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Missing</p>
                                </div>
                            @endif
                        </div>

                        {{-- Income Proof --}}
                        <div class="flex flex-col gap-3">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Income Proof</span>
                            @if($borrower->income_proof)
                                <a href="{{ fetch_data($borrower?->income_proof_url ?? null) }}" target="_blank" class="group flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border-2 border-transparent hover:border-primary transition-all">
                                    <div class="size-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                                        <span class="material-symbols-outlined text-2xl">request_quote</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight">Proof of Income</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Click to view</p>
                                    </div>
                                </a>
                            @else
                                <div class="flex items-center gap-4 p-4 bg-slate-50/50 dark:bg-zinc-800/30 rounded-2xl border-2 border-dashed border-border-main opacity-50">
                                    <div class="size-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-300">
                                        <span class="material-symbols-outlined text-2xl">error</span>
                                    </div>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Missing</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-main">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">work</span>
                        Employment & Income
                    </h3>
                </div>
                <div class="p-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Employment Information</label>
                        @if($isEditing)
                            @if(is_array($employment_information))
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input wire:model="employment_information.employer_name" type="text" placeholder="Employer Name" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20">
                                    <input wire:model="employment_information.job_title" type="text" placeholder="Job Title" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20">
                                    <input wire:model="employment_information.monthly_income" type="number" placeholder="Monthly Income" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20">
                                    <input wire:model="employment_information.employment_status" type="text" placeholder="Status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20">
                                </div>
                            @else
                                <textarea wire:model="employment_information" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20" placeholder="E.g. Senior Software Engineer at Google..."></textarea>
                            @endif
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Employer, Role, and income</p>
                            @error('employment_information') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            @if(is_array($employment_information))
                                <div class="space-y-2">
                                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $employment_information['job_title'] ?? 'N/A' }} at {{ $employment_information['employer_name'] ?? 'N/A' }}</p>
                                    <p class="text-xs text-slate-500 uppercase font-black tracking-tighter">Income: ₦{{ number_format($employment_information['monthly_income'] ?? 0, 2) }} ({{ $employment_information['employment_status'] ?? 'N/A' }})</p>
                                    @if(isset($employment_information['employer_address']))
                                        <p class="text-xs text-slate-400 mt-1 italic">{{ $employment_information['employer_address'] }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300 leading-relaxed">{{ $employment_information ?? 'NO EMPLOYMENT DATA RECORDED' }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

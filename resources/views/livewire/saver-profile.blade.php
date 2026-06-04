<div class="w-full mx-auto space-y-8 p-0">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('customer') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Saver Profile</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Saver Information
                <span class="ml-2 text-sm text-slate-500 font-mono bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md">SV-{{ fetch_data(substr($saver?->id, 0, 8) ?? null) }}</span>
            </h2>
        </div>
        <div class="flex gap-3">
             <button wire:click="toggleEdit" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
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
        <!-- Left Column: Identity & Contact -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-8 text-center">
                <div class="relative inline-block mb-6">
                    <div class="size-32 rounded-full border-4 border-slate-50 dark:border-slate-800 shadow-xl overflow-hidden mx-auto bg-green-50 flex items-center justify-center text-green-600">
                        <span class="material-symbols-outlined text-5xl">person</span>
                    </div>
                </div>

                <div class="space-y-1">
                    @if($isEditing)
                        <input wire:model="name" type="text" class="w-full text-center px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-lg font-black focus:ring-2 focus:ring-primary/20">
                        @error('name') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @else
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $name }}</h3>
                    @endif
                    <p class="text-sm text-green-600 font-black uppercase tracking-widest">Active Saver</p>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800/50 space-y-4">
                    <div class="flex flex-col text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email</span>
                        @if($isEditing)
                            <input wire:model="email" type="email" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-bold focus:ring-2 focus:ring-primary/20">
                            @error('email') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $email ?? 'N/A' }}</span>
                        @endif
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Phone</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $phone }}</span>
                    </div>
                </div>
            </div>

            <!-- Savings Quick Stats -->
            @php $account = $saver->user->savingsAccount; @endphp
            <div class="bg-gradient-to-br from-green-600 to-emerald-800 rounded-2xl p-6 text-white shadow-xl shadow-green-600/20">
                <h4 class="text-sm font-black uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                    Savings Overview
                </h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-60">Current Balance</p>
                        <p class="text-3xl font-black">₦{{ fetch_data($account?->balance?->format() ?? '0.00' ?? null) }}</p>
                    </div>
                    <div class="pt-4 border-t border-white/10">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase opacity-60">Account Number</span>
                            <span class="text-xs font-black font-mono tracking-wider">{{ fetch_data($account?->account_number ?? 'N/A' ?? null) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="{{ fetch_data(route('savings.show', $saver?->user_id) ?? null) }}" class="w-full flex items-center justify-center gap-2 py-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined">history</span>
                View Transaction History
            </a>
        </div>

        <!-- Right Column: Additional Data -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-green-600">contact_page</span>
                        Contact & Support Data
                    </h3>
                </div>
                <div class="p-8 space-y-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Residential Address</label>
                        @if($isEditing)
                            <textarea wire:model="address" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20"></textarea>
                            @error('address') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $address ?: 'NO ADDRESS RECORDED' }}</p>
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

                    @if(!empty($customData))
                        <div class="pt-8 border-t border-slate-50 dark:border-slate-800/50">
                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Additional Metadata</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                @foreach($customData as $key => $value)
                                    @if($key !== 'address')
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}</label>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ is_array($value) ? json_encode($value) : $value }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-zinc-800/30 border border-blue-100 dark:border-zinc-800 rounded-3xl p-6 flex items-start gap-4">
                <div class="bg-blue-100 dark:bg-zinc-800 p-3 rounded-2xl">
                    <span class="material-symbols-outlined text-blue-600">info</span>
                </div>
                <div>
                    <h4 class="text-blue-900 dark:text-zinc-300 font-bold">Saver Profile</h4>
                    <p class="text-blue-700/70 dark:text-zinc-500 text-sm mt-1 leading-relaxed">Savers are primary depositors who do not have loan eligibility by default. To enable borrowing for this user, they must be converted to a Borrower role via the Role Management settings.</p>
                </div>
            </div>
        </div>
    </div>
</div>

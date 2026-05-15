<div class="w-full mx-auto space-y-8 p-0">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('customer') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Guarantor Profile</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Guarantor Information
                <span class="ml-2 text-sm text-slate-500 font-mono bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-md">{{ $guarantor->custom_id }}</span>
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
                    <div class="size-32 rounded-full border-4 border-slate-50 dark:border-slate-800 shadow-xl overflow-hidden mx-auto bg-blue-50 flex items-center justify-center text-blue-600">
                        <span class="material-symbols-outlined text-5xl">shield_person</span>
                    </div>
                </div>

                <div class="space-y-1">
                    @if($isEditing)
                        <input wire:model="name" type="text" class="w-full text-center px-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-lg font-black focus:ring-2 focus:ring-primary/20">
                        @error('name') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    @else
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $name }}</h3>
                    @endif
                    <p class="text-sm text-blue-600 font-black uppercase tracking-widest">Customer Guarantor</p>
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
                        @if($isEditing)
                            <input wire:model="phone" type="text" class="w-full px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-bold focus:ring-2 focus:ring-primary/20">
                            @error('phone') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $phone }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="bg-gradient-to-br from-blue-600 to-indigo-800 rounded-2xl p-6 text-white shadow-xl shadow-blue-600/20">
                <h4 class="text-sm font-black uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">fact_check</span>
                    Active Guarantees
                </h4>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] font-bold uppercase opacity-60">Backing Loans</p>
                        <p class="text-3xl font-black">{{ count($guaranteedLoans) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Additional Data & Guaranteed Loans -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">badge</span>
                        Professional Information
                    </h3>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Residential Address</label>
                        @if($isEditing)
                            <textarea wire:model="address" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20"></textarea>
                        @else
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $address ?: 'NO ADDRESS RECORDED' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Employer</label>
                        @if($isEditing)
                            <input wire:model="employer" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary/20">
                        @else
                            <p class="text-sm font-bold text-slate-900 dark:text-white uppercase">{{ $employer ?: 'N/A' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Monthly Income</label>
                        @if($isEditing)
                            <input wire:model="income" type="number" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold focus:ring-2 focus:ring-primary/20">
                        @else
                            <p class="text-sm font-bold text-slate-900 dark:text-white uppercase">₦{{ number_format($income ?? 0, 2) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- List of Guaranteed Loans -->
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">history</span>
                        Guaranteed Loan History
                    </h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                            <tr>
                                <th class="px-6 py-4">Borrower</th>
                                <th class="px-6 py-4">Loan #</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                                <th class="px-6 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($guaranteedLoans as $loan)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $loan->borrower->user->name }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                        #{{ $loan->loan_number }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900 dark:text-white">
                                        ₦{{ $loan->amount->format() }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tight bg-blue-100 text-blue-700">
                                            {{ $loan->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 italic font-medium">
                                        This guarantor is not currently backing any active loans.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

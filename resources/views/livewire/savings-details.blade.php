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
                <span class="text-slate-800 dark:text-white">Savings Details</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Savings Account</h2>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('export_and_print')
                <a href="{{ fetch_data(route('savings.print', $user?->id) ?? null) }}" target="_blank" class="flex-1 min-w-[120px] flex items-center justify-center gap-2 px-4 py-2 bg-surface border border-border-main text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <span class="material-symbols-outlined text-lg">print</span>
                    Statement
                </a>
            @endcan
            <button wire:click="openTransactionModal('deposit')" class="flex-1 min-w-[140px] flex items-center justify-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-green-600/30 hover:bg-green-700 transition-all">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                Add Deposit
            </button>
        </div>
    </div>

    <div class="flex flex-wrap md:flex-nowrap gap-8 px-1 lg:px-2 pb-8 items-start">
        <!-- Left Column: Customer Summary & Account Summary -->
        <div class="w-full md:w-[320px] lg:w-[380px] shrink-0 space-y-6">
            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
                <div class="p-8 text-center border-b border-slate-50 dark:border-slate-800">
                    <div class="size-24 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4 text-slate-400 border-2 border-white dark:border-slate-700 shadow-lg">
                        <span class="material-symbols-outlined text-4xl">person</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ fetch_data($user?->name ?? null) }}</h3>
                    <p class="text-sm text-slate-500 font-medium uppercase tracking-widest mt-1">{{ fetch_data($user?->getRoleNames()?->first() ?? 'Customer' ?? null) }}</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center text-sm gap-2">
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider whitespace-nowrap">Phone</span>
                        <span class="font-bold text-slate-900 dark:text-white text-right break-all">{{ fetch_data($user?->phone ?? null) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm gap-2">
                        <span class="text-slate-500 font-bold uppercase text-[10px] tracking-wider whitespace-nowrap">Email</span>
                        <span class="font-bold text-slate-900 dark:text-white text-right break-all">{{ fetch_data($user?->email ?? 'N/A' ?? null) }}</span>
                    </div>
                </div>
            </div>

            <!-- Account Status Card -->
            <div class="bg-gradient-to-br from-green-600 to-emerald-800 rounded-2xl p-6 text-white shadow-xl shadow-green-600/20 group relative overflow-hidden">
                <h4 class="text-xs font-black uppercase tracking-widest opacity-60 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">account_balance</span>
                    Account Summary
                </h4>
                <div class="space-y-6 relative z-10">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[120px]">
                            <p class="text-[9px] font-bold uppercase opacity-60 mb-1">Regular Balance</p>
                            <p class="text-xl font-black">₦{{ fetch_data($savingsAccount?->balance?->format() ?? null) }}</p>
                        </div>
                        <div class="flex-1 min-w-[120px] sm:border-l border-white/10 sm:pl-4">
                            <p class="text-[9px] font-bold uppercase opacity-60 mb-1">Daily Thrift</p>
                            <p class="text-xl font-black">₦{{ fetch_data($savingsAccount?->daily_savings_balance?->format() ?? null) }}</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-white/10 space-y-3">
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-[10px] font-bold uppercase opacity-60 whitespace-nowrap">Account Number</span>
                            <span class="text-xs font-black font-mono tracking-wider text-right break-all">{{ fetch_data($savingsAccount?->account_number ?? null) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-[10px] font-bold uppercase opacity-60 whitespace-nowrap">Interest Rate</span>
                            <span class="text-xs font-black text-right">{{ fetch_data($savingsAccount?->interest_rate ?? null) }}% P.A</span>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-10 rotate-12">
                    <span class="material-symbols-outlined text-8xl">account_balance_wallet</span>
                </div>
            </div>

            <button wire:click="openTransactionModal('withdrawal')" class="w-full flex items-center justify-center gap-2 py-4 bg-white dark:bg-slate-800 border border-border-main text-slate-700 dark:text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-sm hover:shadow-md transition-all">
                <span class="material-symbols-outlined">payments</span>
                Withdraw Funds
            </button>
        </div>

        <!-- Right Column: Transactions Table -->
        <div class="flex-1 min-w-0 space-y-6">
            <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 dark:text-white">Recent Transactions</h3>
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-green-500"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Live Updates</span>
                    </div>
                </div>
                <div class="p-0 overflow-x-auto overflow-y-auto max-h-[600px]">
                    <table class="w-full text-left text-sm min-w-[600px]">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-black uppercase text-slate-400 tracking-widest sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4">Transaction Date</th>
                                <th class="px-6 py-4">Type</th>
                                <th class="px-6 py-4">Reference</th>
                                <th class="px-6 py-4 text-right">Amount</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-slate-900 dark:text-white text-xs whitespace-nowrap">
                                            {{ fetch_data(\Carbon\Carbon::parse($trx?->transaction_date)?->format('M d, Y') ?? null) }}
                                        </p>
                                        <p class="text-[10px] text-slate-500 font-medium tracking-tight">
                                            {{ fetch_data(\Carbon\Carbon::parse($trx?->transaction_date)?->format('h:i A') ?? null) }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tight {{ fetch_data(($trx?->type === 'deposit' || $trx?->type === 'daily_thrift') ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?? null) }}">
                                            {{ fetch_data(str_replace('_', ' ', $trx?->type) ?? null) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[10px] text-slate-500 uppercase">
                                        {{ fetch_data($trx?->reference ?? null) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-black text-sm {{ fetch_data(($trx?->type === 'deposit' || $trx?->type === 'daily_thrift') ? 'text-green-600' : 'text-amber-600' ?? null) }}">
                                            {{ fetch_data(($trx?->type === 'deposit' || $trx?->type === 'daily_thrift') ? '+' : '-' ?? null) }}₦{{ fetch_data($trx?->amount?->format() ?? null) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if(!$trx->repayment_id)
                                            <button 
                                                wire:click="deleteTransaction('{{ fetch_data($trx?->id ?? null) }}')" 
                                                wire:confirm="Are you sure you want to delete this transaction and adjust the balance?"
                                                class="size-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all"
                                            >
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        @else
                                            <span class="material-symbols-outlined text-lg text-slate-200" title="Linked to loan repayment">lock</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($trx->notes)
                                    <tr class="bg-slate-50/50 dark:bg-slate-800/10">
                                        <td colspan="5" class="px-6 py-2">
                                            <p class="text-[10px] text-slate-400 italic">Note: {{ fetch_data($trx?->notes ?? null) }}</p>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic font-medium">
                                        No transactions recorded for this account.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800">
                    {{ fetch_data($transactions?->links() ?? null) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    @if($showTransactionModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-8 py-6 border-b border-slate-50 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">Record {{ ucfirst($transactionType) }}</h3>
                    <button wire:click="$set('showTransactionModal', false)" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit.prevent="submitTransaction" class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Source Account</label>
                        <select wire:model="sourceAccount" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-xs font-black focus:ring-2 focus:ring-primary/20" required>
                            <option value="regular">Regular Savings</option>
                            <option value="daily_thrift">Daily Savings (Thrift)</option>
                        </select>
                        @error('sourceAccount') <span class="text-[10px] font-bold text-red-500 mt-1 block px-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Amount (₦)</label>
                        <input wire:model="amount" type="number" step="0.01" class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-lg font-black focus:ring-2 focus:ring-primary/20" required>
                        @error('amount') <span class="text-[10px] font-bold text-red-500 mt-1 block px-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Payment Method</label>
                            <select wire:model="paymentMethod" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Date</label>
                            <input wire:model="transactionDate" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Reference</label>
                        <input wire:model="reference" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Notes</label>
                        <textarea wire:model="notes" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-medium focus:ring-2 focus:ring-primary/20" placeholder="Optional transaction details..."></textarea>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="submit" class="flex-1 py-4 {{ $transactionType === 'deposit' ? 'bg-green-600 shadow-green-600/20' : 'bg-amber-600 shadow-amber-600/20' }} text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-lg hover:scale-[1.02] active:scale-95 transition-all">
                            Save {{ ucfirst($transactionType) }}
                        </button>
                        <button type="button" wire:click="$set('showTransactionModal', false)" class="flex-1 py-4 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-[0.2em] hover:bg-slate-200 transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

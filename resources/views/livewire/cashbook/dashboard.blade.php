<div class="min-h-screen bg-background-light py-8 px-4 sm:px-6 md:px-8">
    @php
        $cashTarget = $entry->total_inflow->subtract($entry->expected_bank_transfers);
        $cashVariance = $cashTarget->subtract($entry->actual_cash_at_hand);
        
        $diff = $entry->daily_net;
        $isBalanced = $diff->isZero();
        $isShortage = $diff->isNegative();
        $isSurplus = $diff->isPositive();

        $currentClass = match($entry->status) {
            'verified' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50',
            'pending' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-800/50',
            'discrepancy' => 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-800/50',
            default => 'bg-slate-50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-400 border-border-main'
        };

        $canUnlock = auth()->user()->isAdmin() || 
                     ($entry->status === 'verified' && 
                      auth()->user()->organization->allow_staff_cashbook_unlock && 
                      $entry->staff_unlock_count < auth()->user()->organization->cashbook_unlock_limit);
    @endphp
    {{-- Fixed Back Button --}}
    <a href="{{ route('records') }}" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 dark:bg-slate-800/30 backdrop-blur-md border border-white/20 dark:border-slate-700/50 rounded-full text-primary dark:text-white hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </a>

    {{-- Header Section --}}
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <h1 class="text-2xl font-bold text-primary dark:text-white tracking-tight">Digital Cashbook</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">A premium digital ledger for tracking daily vault flows and reconciliation.</p>
            </div>
            
            {{-- Responsive Action Bar --}}
            <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-3 w-full md:w-auto">
                    {{-- Month Button --}}
                    <div class="flex-1 md:flex-initial">
                        <a href="{{ route('cashbook.month-record', ['date' => $currentDate]) }}" 
                            class="flex items-center justify-center px-4 py-2.5 border border-border-main text-[10px] font-black uppercase tracking-widest rounded-sm bg-surface text-slate-700 dark:text-white hover:bg-background-light transition-all shadow-sm w-full md:w-auto">
                            <svg class="w-4 h-4 mr-2 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Month
                        </a>
                    </div>

                    {{-- Verify Button --}}
                    <div class="flex-[2] md:flex-initial">
                        <button wire:click="verify" @if($entry->status === 'verified') disabled @endif
                            class="flex items-center justify-center w-full px-6 py-2.5 border border-transparent text-[10px] font-black uppercase tracking-widest rounded-sm shadow-lg text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:bg-slate-400 dark:disabled:bg-slate-800 transition-all whitespace-nowrap">
                            @if($entry->status === 'verified')
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Verified
                            @else
                                Verify & Lock
                            @endif
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    {{-- Date Input --}}
                    <div class="flex-1 md:w-auto md:min-w-[180px]">
                        <input type="date" wire:model.live="currentDate" wire:change="loadEntry" 
                            class="block w-full pl-4 pr-3 py-2 border border-border-main rounded-sm leading-5 bg-surface font-bold text-primary dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm shadow-sm">
                    </div>

                    {{-- Unlock Button --}}
                    @if($entry->status === 'verified' && $canUnlock)
                        <div class="w-auto">
                            <button wire:click="unlock" wire:confirm="Are you sure you want to unlock this record? This will allow changes to the ledger."
                                class="flex items-center justify-center px-3 py-2 border border-rose-200 dark:border-rose-900/30 text-rose-600 bg-surface hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-sm shadow-sm transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                @if(!auth()->user()->isAdmin())
                                    <span class="ml-2 text-[9px] font-black uppercase">Trial {{ fetch_data($entry?->staff_unlock_count ?? null) }}/{{ fetch_data(auth()?->user()?->organization?->cashbook_unlock_limit ?? null) }}</span>
                                @endif
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        @if($errorMessage)
            <div class="mt-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-900/30 rounded-sm flex items-start space-x-3 text-rose-800 dark:text-rose-200 animate-pulse">
                <svg class="w-5 h-5 mt-0.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-bold leading-relaxed">{{ $errorMessage }}</p>
            </div>
        @endif
    </div>

    {{-- Stats Summary Area --}}
    <div class="max-w-7xl mx-auto space-y-4 mb-10">
        <div class="flex flex-wrap gap-4">
            @can('view_live_balance')
                <div class="flex-1 min-w-[240px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-5 transition hover:shadow-md border-l-4 border-l-blue-500">
                    <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Live Account Balance</dt>
                    <dd class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ fetch_data($accountBalance?->format() ?? null) }}</dd>
                </div>
            @endcan
            <div class="flex-1 min-w-[240px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-5 transition hover:shadow-md">
                <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Inflow</dt>
                <dd class="text-2xl font-black text-emerald-600 dark:text-emerald-500">+{{ fetch_data($entry?->total_inflow?->format() ?? null) }}</dd>
            </div>
            <div class="flex-1 min-w-[240px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-5 transition hover:shadow-md">
                <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Outflow</dt>
                <dd class="text-2xl font-black text-rose-600 dark:text-rose-500">-{{ fetch_data($entry?->total_outflow?->format() ?? null) }}</dd>
            </div>
            <div class="flex-1 min-w-[240px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-5 transition hover:shadow-md">
                <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Expected Deposit</dt>
                <dd class="text-2xl font-black text-primary dark:text-white">{{ fetch_data($entry?->expected_deposit?->format() ?? null) }}</dd>
            </div>
        </div>

        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[300px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-6 transition hover:shadow-md border-l-4 border-l-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Daily Bank Deposit</dt>
                        <dd class="text-2xl font-black text-emerald-600 dark:text-emerald-500">{{ fetch_data($entry?->bank_deposit_amount?->format() ?? null) }}</dd>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Expected Transfer</span>
                        <p class="text-xl font-black text-emerald-800 dark:text-emerald-400">
                            {{ fetch_data($entry?->expected_bank_transfers?->format() ?? null) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-[300px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-6 transition hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Physical Cash (Hand)</dt>
                        <dd class="text-2xl font-black text-primary dark:text-white">{{ fetch_data($entry?->actual_cash_at_hand?->format() ?? null) }}</dd>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Recon Variance</span>
                        <p class="text-xl font-black {{ fetch_data($cashVariance?->isPositive() ? 'text-rose-600' : ($cashVariance?->isNegative() ? 'text-emerald-600' : 'text-primary dark:text-white') ?? null) }}">
                            {{ fetch_data($cashVariance?->format() ?? null) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-[300px] bg-surface overflow-hidden shadow-sm border border-border-main rounded-sm p-6 transition hover:shadow-md border-l-4 {{ fetch_data($remainingBudget?->isNegative() ? 'border-l-rose-500' : 'border-l-amber-500' ?? null) }}">
                <dt class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Remaining Expense Budget</dt>
                <dd class="text-2xl font-black {{ fetch_data($remainingBudget?->isNegative() ? 'text-rose-600' : 'text-amber-600' ?? null) }}">
                    {{ fetch_data($remainingBudget?->format() ?? null) }}
                </dd>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase mt-1 tracking-tighter">Monthly Limit Status</p>
            </div>
        </div>
    </div>

    {{-- Main Ledger Content --}}
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-surface border border-border-main rounded-sm shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
            {{-- Ledger Title Bar --}}
            <div class="px-8 py-5 border-b border-border-main bg-background-light/30 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-surface p-2 rounded-sm shadow-sm border border-border-main">
                        <span class="text-xs font-black text-primary dark:text-white uppercase tracking-tighter">{{ fetch_data($entry?->entry_date?->format('D') ?? null) }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white tracking-tight">{{ fetch_data($entry?->entry_date?->format('l, d F Y') ?? null) }}</h3>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $currentClass }}">
                        {{ fetch_data($entry?->status ?? null) }}
                    </span>
                </div>
            </div>

            <div class="p-8">
                {{-- Inflows & Outflows --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12">
                    {{-- Inflows --}}
                    <div class="space-y-8">
                        <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>
                            Daily Cash Inflows
                        </h4>
                        
                        <div class="space-y-1 bg-background-light/50 p-6 rounded-sm border border-border-main">
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Loan Repayments</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->loan_repayments?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-emerald-600 font-bold uppercase tracking-tight">Loan Interest</span>
                                <span class="text-sm font-black text-emerald-600 font-mono">+{{ fetch_data($entry?->loan_interest?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Processing Fees</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->loan_processing_fees?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Insurance Fees</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->insurance_fees?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Savings Deposits</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->savings_deposits?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Daily Savings</span>
                                <span class="text-sm font-black text-blue-600 dark:text-blue-400 font-mono">{{ fetch_data($entry?->daily_savings?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Registration Fees</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->registration_fees?->format() ?? null) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-border-main">
                            <div class="group">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Card Payments</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.card_payments" @if($entry->status === 'verified') disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-surface p-2.5 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm font-black text-emerald-600 dark:text-emerald-400">
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Excess Cash</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.excess_cash" @if($entry->status === 'verified') disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-surface p-2.5 shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm font-black text-emerald-600 dark:text-emerald-400">
                            </div>
                        </div>
                    </div>

                    {{-- Outflows --}}
                    <div class="space-y-8 md:border-l md:border-border-main md:pl-12">
                        <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span>
                            Daily Cash Outflows
                        </h4>

                        <div class="space-y-1 bg-background-light/50 p-6 rounded-sm border border-border-main">
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Disbursements</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->loan_disbursements?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Withdrawals</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->savings_withdrawals?->format() ?? null) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-border-main">
                            <div class="group">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Daily Expenses</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.daily_expense_amount" @if($entry->status === 'verified') disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-surface p-2.5 shadow-sm focus:ring-rose-500 focus:border-rose-500 text-sm font-black text-rose-600 dark:text-rose-400">
                                <p class="text-[8px] text-slate-400 dark:text-slate-500 font-bold px-1 mt-1">Deducted from Monthly Budget</p>
                            </div>
                            <div class="group">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Bank Out</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.bank_withdrawals" @if($entry->status === 'verified') disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-surface p-2.5 shadow-sm focus:ring-rose-500 focus:border-rose-500 text-sm font-black text-rose-600 dark:text-rose-400">
                            </div>
                            <div class="{{ fetch_data(!auth()?->user()?->isAdmin() ? 'opacity-40 pointer-events-none' : '' ?? null) }}">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-tighter">Charges</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.charges" @if($entry->status === 'verified' || !auth()->user()->isAdmin()) disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-surface p-2.5 text-xs font-black text-rose-600 dark:text-rose-400">
                            </div>
                            <div class="{{ fetch_data(!auth()?->user()?->isAdmin() ? 'opacity-40 pointer-events-none' : '' ?? null) }}">
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-tighter">Bonuses</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.bonuses" @if($entry->status === 'verified' || !auth()->user()->isAdmin()) disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-surface p-2.5 text-xs font-black text-rose-600 dark:text-rose-400">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vault Verification --}}
                <div class="pt-8 border-t border-border-main space-y-8">
                    <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] flex items-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                        Vault Verification
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div class="bg-blue-50/40 dark:bg-blue-900/10 p-6 rounded-sm border border-blue-100 dark:border-blue-900/30 shadow-inner">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[9px] font-black text-blue-800 dark:text-blue-300 uppercase tracking-widest mb-3 text-center">Physical Cash Count</label>
                                    <div class="relative group">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-blue-400 font-black text-xs">₦</span>
                                        </div>
                                        <input type="number" step="0.01" wire:model.blur="manualFields.actual_cash_at_hand" @if($entry->status === 'verified') disabled @endif
                                            class="w-full pl-8 pr-3 py-3 rounded-sm border-blue-200 dark:border-blue-900/30 bg-surface shadow-md focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 text-lg font-black text-blue-900 dark:text-blue-100 transition-all text-center">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[9px] font-black text-amber-800 dark:text-amber-300 uppercase tracking-widest mb-3 text-center">Expected Physical Cash</label>
                                    <div class="w-full px-3 py-3 rounded-sm border-amber-200 dark:border-amber-900/30 bg-amber-50/50 dark:bg-amber-900/10 text-lg font-black text-amber-700 dark:text-amber-400 text-center border shadow-sm h-[52px] flex items-center justify-center">
                                        {{ fetch_data($entry?->expected_cash_at_hand?->format() ?? null) }}
                                    </div>
                                </div>

                                <div class="md:col-span-2 pt-4 border-t border-blue-100/50 dark:border-blue-900/30">
                                    <label class="block text-[9px] font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-widest mb-3 text-center">Total Bank Deposit</label>
                                    <div class="relative group max-w-md mx-auto">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-emerald-400 font-black text-xs">₦</span>
                                        </div>
                                        <input type="number" step="0.01" wire:model.blur="manualFields.bank_deposit_amount" @if($entry->status === 'verified') disabled @endif
                                            class="w-full pl-8 pr-3 py-3 rounded-sm border-emerald-200 dark:border-emerald-900/30 bg-surface shadow-md focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-lg font-black text-emerald-700 dark:text-emerald-300 transition-all text-center">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Discrepancy Insight --}}
                        <div class="space-y-4">
                            <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">Reconciliation Insight</label>
                            
                            <div class="p-6 rounded-sm border {{ $isBalanced ? 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-100 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-200' : ($isShortage ? 'bg-rose-50 dark:bg-rose-900/10 border-rose-100 dark:border-rose-900/30 text-rose-800 dark:text-rose-200' : 'bg-amber-50 dark:bg-amber-900/10 border-amber-100 dark:border-amber-900/30 text-amber-800 dark:text-amber-200') }}">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60">Vault Variance</span>
                                    <span class="text-lg font-black {{ fetch_data($cashVariance?->isPositive() ? 'text-rose-600' : ($cashVariance?->isNegative() ? 'text-emerald-600' : '') ?? null) }}">
                                        {{ fetch_data($cashVariance?->format() ?? null) }}
                                    </span>
                                </div>
                                <p class="text-[13px] font-bold leading-relaxed">
                                    @if($isBalanced)
                                        Today's bank deposit perfectly accounts for all daily inflows.
                                    @elseif($isShortage)
                                        The bank deposit is missing <span class="font-black underline">{{ fetch_data($diff?->absolute()?->format() ?? null) }}</span> compared to the total inflows received.
                                    @else
                                        There is an excess of <span class="font-black underline">{{ fetch_data($diff?->absolute()?->format() ?? null) }}</span> in the bank deposit account.
                                    @endif
                                </p>
                            </div>
                            
                            <div class="pt-2">
                                <label class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-3 px-1">Daily Journal Observations</label>
                                <textarea wire:model.blur="manualFields.description" placeholder="Summarize today's vault activity..." @if($entry->status === 'verified') disabled @endif
                                    class="block w-full rounded-sm border-border-main bg-background-light/50 dark:bg-slate-800/50 text-primary dark:text-white shadow-inner focus:ring-blue-500 focus:border-blue-500 text-sm font-medium min-h-[100px] p-4 resize-none italic"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($entry->shortfall_report)
                <div class="px-8 py-6 bg-rose-50/50 dark:bg-rose-900/10 border-t border-rose-100 dark:border-rose-900/30 flex items-center space-x-4">
                    <div class="bg-rose-100 dark:bg-rose-900/30 p-2 rounded-sm">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-rose-500 dark:text-rose-400 uppercase tracking-widest mb-0.5">Shortfall Reconciliation Report</span>
                        <p class="text-xs font-bold text-rose-800 dark:text-rose-200 leading-relaxed italic">"{{ fetch_data($entry?->shortfall_report ?? null) }}"</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-center py-4">
            <span class="h-px w-24 bg-border-main"></span>
            <span class="mx-4 text-[10px] font-black text-slate-300 dark:text-slate-700 uppercase tracking-[0.5em]">Digital Ledger</span>
            <span class="h-px w-24 bg-border-main"></span>
        </div>
    </div>

    {{-- Shortfall Modal --}}
    @if($showShortfallModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 dark:bg-black/60 backdrop-blur-md">
            <div class="bg-surface rounded-sm shadow-2xl max-w-md w-full overflow-hidden border border-border-main transform transition-all animate-in fade-in zoom-in duration-300">
                <div class="p-10 text-center">
                    <div class="bg-rose-50 dark:bg-rose-900/20 w-20 h-20 rounded-sm flex items-center justify-center mx-auto mb-6 border border-rose-100 dark:border-rose-900/30">
                        <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-2xl font-black text-primary dark:text-white uppercase tracking-tighter mb-4">Mandatory Report</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed mb-8">
                        The bank deposit is significantly less than the total inflows received today. An explanation for this discrepancy is required.
                    </p>
                    
                    <textarea wire:model="shortfall_report" 
                        placeholder="Detail the reason for the discrepancy..."
                        class="w-full rounded-sm border-border-main bg-background-light/50 dark:bg-slate-800/50 text-primary dark:text-white focus:ring-rose-500 focus:border-rose-500 text-sm font-medium p-5 min-h-[150px] shadow-inner mb-8 italic"></textarea>
                    
                    <div class="flex flex-col space-y-3">
                        <button wire:click="submitShortfallReport" class="w-full py-4 bg-rose-600 text-white rounded-sm text-xs font-black uppercase tracking-widest hover:bg-rose-700 shadow-xl shadow-rose-200 transition-all transform hover:-translate-y-1 active:translate-y-0">Submit & Finalize</button>
                        <button wire:click="$set('showShortfallModal', false)" class="w-full py-4 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest hover:text-slate-600 transition-colors">Go Back</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

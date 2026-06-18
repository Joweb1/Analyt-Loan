<div class="min-h-screen bg-background-light py-8 px-4 sm:px-6 md:px-8">
    <livewire:cashbook.validation-modal :entry="$entry" />

    @php
        $cashTarget = $entry->total_inflow->subtract($entry->expected_bank_transfers);
        $cashVariance = $cashTarget->subtract($entry->actual_cash_at_hand);
        
        $diff = $entry->daily_net;
        $isBalanced = $diff->isZero();
        $isShortage = $diff->isNegative();
        $isSurplus = $diff->isPositive();

        $isVerified = $entry->status === 'verified';
        
        $currentClass = match($entry->status) {
            'verified' => 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/50',
            'pending' => 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border-amber-100 dark:border-amber-800/50',
            'discrepancy' => 'bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 border-rose-100 dark:border-rose-900/30',
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
                <h1 class="text-2xl font-bold text-primary dark:text-white tracking-tight">Daily Digital Cashbook</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Strict vault management and daily reconciliation ledger.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('cashbook.month-record', ['date' => $currentDate]) }}" 
                    class="flex items-center px-4 py-2.5 border border-border-main text-[10px] font-black uppercase tracking-widest rounded-sm bg-surface text-slate-700 dark:text-white hover:bg-background-light transition-all shadow-sm">
                    Month Record
                </a>
                
                {{-- Date Input --}}
                <input type="date" wire:model.live="currentDate" wire:change="loadEntry" 
                    class="block pl-4 pr-3 py-2.5 border border-border-main rounded-sm bg-surface font-bold text-primary dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm shadow-sm">
            </div>
        </div>
    </div>

    {{-- Main Ledger Content --}}
    <div class="max-w-7xl mx-auto space-y-6">
        <div class="bg-surface border border-border-main rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden {{ $isVerified ? 'ring-4 ring-emerald-500/20 shadow-emerald-500/10' : '' }}">
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
                    @if($entry->status === 'verified' && $canUnlock)
                        <button wire:click="unlock" wire:confirm="Are you sure you want to unlock this record?"
                            class="text-rose-600 hover:text-rose-700 transition-colors">
                            <span class="material-symbols-outlined">lock_open</span>
                        </button>
                    @endif
                </div>
            </div>

            <div class="p-8">
                {{-- Vault Verification (Restored to top) --}}
                <div class="mb-12">
                    <h4 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] flex items-center mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>
                        Vault Verification
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div class="bg-blue-50/40 dark:bg-blue-900/10 p-6 rounded-sm border border-blue-100 dark:border-blue-900/30 shadow-inner">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[9px] font-black text-blue-800 dark:text-blue-300 uppercase tracking-widest mb-3 text-center">Physical Cash Count</label>
                                    <input type="number" step="0.01" wire:model.blur="manualFields.actual_cash_at_hand" @if($isVerified) disabled @endif
                                        class="w-full py-3 rounded-sm border-blue-200 bg-surface text-lg font-black text-blue-900 text-center">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-amber-800 dark:text-amber-300 uppercase tracking-widest mb-3 text-center">Expected Physical Cash</label>
                                    <div class="w-full py-3 rounded-sm border-amber-200 bg-amber-50/50 text-lg font-black text-amber-700 text-center border shadow-sm">
                                        {{ fetch_data($entry?->expected_cash_at_hand?->format() ?? null) }}
                                    </div>
                                </div>
                                <div class="md:col-span-2 pt-4 border-t border-blue-100/50">
                                    <label class="block text-[9px] font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-widest mb-3 text-center">Total Bank Deposit</label>
                                    <input type="number" step="0.01" wire:model.blur="manualFields.bank_deposit_amount" @if($isVerified) disabled @endif
                                        class="w-full py-3 rounded-sm border-emerald-200 bg-surface text-lg font-black text-emerald-700 text-center">
                                </div>
                            </div>
                        </div>
                        <div class="p-6 rounded-sm border {{ $isBalanced ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : ($isShortage ? 'bg-rose-50 border-rose-100 text-rose-800' : 'bg-amber-50 border-amber-100 text-amber-800') }}">
                             <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60">Vault Variance</span>
                                <span class="text-lg font-black">{{ fetch_data($cashVariance?->format() ?? null) }}</span>
                            </div>
                            <p class="text-[13px] font-bold leading-relaxed">
                                @if($isBalanced) Today's vault matches perfectly.
                                @elseif($isShortage) The vault is short <span class="font-black underline">{{ fetch_data($diff?->absolute()?->format() ?? null) }}</span>.
                                @else There is an excess of <span class="font-black underline">{{ fetch_data($diff?->absolute()?->format() ?? null) }}</span> in the vault.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Inflows & Outflows Structure --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-12 border-t border-border-main pt-12">
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
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Registration Fees</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->registration_fees?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Other Inflow</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->default_amount?->format() ?? null) }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Card Payments</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.card_payments" @if($isVerified) disabled @endif class="p-2.5 w-full rounded-sm border-border-main bg-surface text-sm font-black text-emerald-600">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Excess Cash</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.excess_cash" @if($isVerified) disabled @endif class="p-2.5 w-full rounded-sm border-border-main bg-surface text-sm font-black text-emerald-600">
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
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Withdrawals</span>
                                <span class="text-sm font-black text-primary dark:text-white font-mono">{{ fetch_data($entry?->savings_withdrawals?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5 border-b border-border-main">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Charges</span>
                                <span class="text-sm font-black text-rose-600 font-mono">{{ fetch_data($entry?->charges?->format() ?? null) }}</span>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 uppercase tracking-tight">Bonuses</span>
                                <span class="text-sm font-black text-rose-600 font-mono">{{ fetch_data($entry?->bonuses?->format() ?? null) }}</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Daily Expenses</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.daily_expense_amount" @if($isVerified) disabled @endif class="p-2.5 w-full rounded-sm border-border-main bg-surface text-sm font-black text-rose-600">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-2 tracking-widest px-1">Bank Out (Admin Only)</label>
                                <input type="number" step="0.01" wire:model.blur="manualFields.bank_withdrawals" @if($isVerified || !auth()->user()->isAdmin()) disabled @endif class="p-2.5 w-full rounded-sm border-border-main bg-surface text-sm font-black text-rose-600">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shortfall Report Display --}}
                <div class="mt-8 p-6 bg-slate-50 dark:bg-slate-900/50 border border-border-main rounded-sm">
                    <h5 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Shortfall Reconciliation Report</h5>
                    <textarea wire:model.blur="manualFields.shortfall_report" @if($isVerified) disabled @endif
                        placeholder="Provide details about any physical cash or bank shortfall recorded for the day..."
                        class="w-full p-3 rounded-sm border-border-main bg-surface text-sm font-medium text-slate-700 dark:text-slate-300 italic min-h-[80px] focus:ring-1 focus:ring-primary focus:border-primary"></textarea>
                </div>

                {{-- Error Message Display --}}
                @if($errorMessage)
                    <div class="mt-4 p-4 bg-red-100 text-red-800 rounded-sm font-bold text-sm border-l-4 border-red-600">
                        {{ $errorMessage }}
                    </div>
                @endif

                {{-- Confirm & Close Day Button (Fearful Styling) --}}
                @if(!$isVerified)
                    <div class="mt-8 pt-8 border-t-2 border-dashed border-red-200 text-center">
                        <button wire:click="verify" 
                            class="w-full py-6 bg-red-600 text-white font-black uppercase tracking-[0.3em] text-xl rounded-sm shadow-2xl shadow-red-600/50 hover:bg-red-700 hover:scale-[1.01] transition-all animate-pulse">
                            CONFIRM & CLOSE DAY
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stats Summary Area (Restored to bottom) --}}
        <div class="bg-surface rounded-2xl border border-border-main p-8 grid grid-cols-1 md:grid-cols-4 gap-6">
             <div class="p-5 border-l-4 border-l-blue-500">
                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Live Account Balance</dt>
                <dd class="text-2xl font-black text-blue-600">{{ fetch_data($accountBalance?->format() ?? null) }}</dd>
            </div>
            <div class="p-5 border-l-4 border-l-emerald-500">
                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Inflow</dt>
                <dd class="text-2xl font-black text-emerald-600">+{{ fetch_data($entry?->total_inflow?->format() ?? null) }}</dd>
            </div>
            <div class="p-5 border-l-4 border-l-rose-500">
                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Outflow</dt>
                <dd class="text-2xl font-black text-rose-600">-{{ fetch_data($entry?->total_outflow?->format() ?? null) }}</dd>
            </div>
            <div class="p-5 border-l-4 border-l-primary">
                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Expected Deposit</dt>
                <dd class="text-2xl font-black text-primary">{{ fetch_data($entry?->expected_deposit?->format() ?? null) }}</dd>
            </div>
             <div class="p-5 border-l-4 border-l-amber-500">
                <dt class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Remaining Expense Budget</dt>
                <dd class="text-2xl font-black text-amber-600">{{ fetch_data($remainingBudget?->format() ?? null) }}</dd>
            </div>
        </div>
    </div>
</div>

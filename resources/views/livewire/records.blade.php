<div class="w-full py-8 px-2">
    <div class="mb-8 px-2">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Records Hub</h1>
        <p class="mt-1 text-xs text-slate-500 font-medium tracking-wide">Select a digital record book to view or manage entries.</p>
    </div>

    @can('view_records_hub_stats')
    <div class="mb-8 px-2 flex flex-wrap gap-4">
        {{-- Total Savings Balance Card --}}
        <div class="flex-1 min-w-[280px] bg-surface p-6 rounded-2xl border border-border-main shadow-sm relative group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1">Total Savings</h3>
                    <p class="text-2xl font-black text-slate-900 dark:text-white tracking-tight italic">₦{{ fetch_data($savingsBalance?->format() ?? null) }}</p>
                </div>
                <div class="relative" x-data="{ open: false }" wire:key="savings-filter">
                    <button @click="open = !open" class="size-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-primary/10 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-xl">tune</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-3 w-52 bg-surface border border-border-main rounded-2xl shadow-2xl z-50 p-2 overflow-hidden">
                        <div class="px-3 py-2 border-b border-slate-50 dark:border-slate-800 mb-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select View Period</span>
                        </div>
                        <button wire:click="$set('savingsPeriod', 'today'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $savingsPeriod === 'today' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">today</span> Today
                        </button>
                        <button wire:click="$set('savingsPeriod', 'this_week'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $savingsPeriod === 'this_week' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">date_range</span> This Week
                        </button>
                        <button wire:click="$set('savingsPeriod', 'this_month'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $savingsPeriod === 'this_month' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">calendar_month</span> This Month
                        </button>
                        <button @click="open = false" wire:click="$set('savingsPeriod', 'custom')" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $savingsPeriod === 'custom' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">history_toggle_off</span> Custom Range
                        </button>
                    </div>
                </div>
            </div>
            @if($savingsPeriod === 'custom')
                <div class="flex gap-2 mt-4 animate-in slide-in-from-top-2 duration-300">
                    <input type="date" wire:model.live="customSavingsStart" class="flex-1 text-[10px] p-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg font-bold">
                    <input type="date" wire:model.live="customSavingsEnd" class="flex-1 text-[10px] p-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg font-bold">
                </div>
            @endif
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity overflow-hidden size-32 pointer-events-none rounded-2xl">
                <span class="material-symbols-outlined text-[100px]">account_balance_wallet</span>
            </div>
        </div>

        {{-- Total Daily Savings (Thrift) Card --}}
        <div class="flex-1 min-w-[280px] bg-surface p-6 rounded-2xl border border-border-main shadow-sm relative group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1">Total Daily Thrift</h3>
                    <p class="text-2xl font-black text-indigo-600 tracking-tight italic">₦{{ fetch_data($thriftBalance?->format() ?? null) }}</p>
                </div>
                <div class="relative" x-data="{ open: false }" wire:key="thrift-filter">
                    <button @click="open = !open" class="size-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-primary/10 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-xl">tune</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-3 w-52 bg-surface border border-border-main rounded-2xl shadow-2xl z-50 p-2 overflow-hidden">
                        <div class="px-3 py-2 border-b border-slate-50 dark:border-slate-800 mb-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select View Period</span>
                        </div>
                        <button wire:click="$set('thriftPeriod', 'today'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $thriftPeriod === 'today' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">today</span> Today
                        </button>
                        <button wire:click="$set('thriftPeriod', 'this_week'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $thriftPeriod === 'this_week' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">date_range</span> This Week
                        </button>
                        <button wire:click="$set('thriftPeriod', 'this_month'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $thriftPeriod === 'this_month' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">calendar_month</span> This Month
                        </button>
                        <button @click="open = false" wire:click="$set('thriftPeriod', 'custom')" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $thriftPeriod === 'custom' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">history_toggle_off</span> Custom Range
                        </button>
                    </div>
                </div>
            </div>
            @if($thriftPeriod === 'custom')
                <div class="flex gap-2 mt-4 animate-in slide-in-from-top-2 duration-300">
                    <input type="date" wire:model.live="customThriftStart" class="flex-1 text-[10px] p-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg font-bold">
                    <input type="date" wire:model.live="customThriftEnd" class="flex-1 text-[10px] p-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg font-bold">
                </div>
            @endif
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity overflow-hidden size-32 pointer-events-none rounded-2xl">
                <span class="material-symbols-outlined text-[100px]">savings</span>
            </div>
        </div>

        {{-- Total Loan Repayments Card --}}
        <div class="flex-1 min-w-[280px] bg-surface p-6 rounded-2xl border border-border-main shadow-sm relative group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1">Total Loan Repayments</h3>
                    <p class="text-2xl font-black text-emerald-600 tracking-tight italic">₦{{ fetch_data($loanRepayments?->format() ?? null) }}</p>
                </div>
                <div class="relative" x-data="{ open: false }" wire:key="loan-filter">
                    <button @click="open = !open" class="size-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-primary/10 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-xl">tune</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         class="absolute right-0 mt-3 w-52 bg-surface border border-border-main rounded-2xl shadow-2xl z-50 p-2 overflow-hidden">
                        <div class="px-3 py-2 border-b border-slate-50 dark:border-slate-800 mb-1">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Select View Period</span>
                        </div>
                        <button wire:click="$set('loanPeriod', 'today'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $loanPeriod === 'today' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">today</span> Today
                        </button>
                        <button wire:click="$set('loanPeriod', 'this_week'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $loanPeriod === 'this_week' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">date_range</span> This Week
                        </button>
                        <button wire:click="$set('loanPeriod', 'this_month'); open = false;" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $loanPeriod === 'this_month' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">calendar_month</span> This Month
                        </button>
                        <button @click="open = false" wire:click="$set('loanPeriod', 'custom')" class="w-full text-left px-4 py-2.5 text-xs font-bold rounded-xl transition-colors flex items-center gap-3 {{ $loanPeriod === 'custom' ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                            <span class="material-symbols-outlined text-sm">history_toggle_off</span> Custom Range
                        </button>
                    </div>
                </div>
            </div>
            @if($loanPeriod === 'custom')
                <div class="flex gap-2 mt-4 animate-in slide-in-from-top-2 duration-300">
                    <input type="date" wire:model.live="customLoanStart" class="flex-1 text-[10px] p-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg font-bold">
                    <input type="date" wire:model.live="customLoanEnd" class="flex-1 text-[10px] p-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg font-bold">
                </div>
            @endif
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity overflow-hidden size-32 pointer-events-none rounded-2xl">
                <span class="material-symbols-outlined text-[100px]">payments</span>
            </div>
        </div>

        {{-- Org. Total Balance Card --}}
        <div class="flex-1 min-w-[280px] bg-surface p-6 rounded-2xl border border-border-main shadow-sm relative group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-[0.2em] mb-1">Org. Total Balance</h3>
                    <p class="text-2xl font-black text-primary tracking-tight italic">₦{{ fetch_data($totalBalance?->format() ?? '0.00') }}</p>
                </div>
                <div class="p-1 bg-primary/5 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-xl">account_balance</span>
                </div>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Total Portfolio Outstanding</p>
            <div class="absolute -right-4 -bottom-4 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity overflow-hidden size-32 pointer-events-none rounded-2xl">
                <span class="material-symbols-outlined text-[100px]">account_balance</span>
            </div>
        </div>
    </div>
    @endcan


    <div class="grid grid-cols-[repeat(auto-fill,minmax(240px,1fr))] gap-3">
        {{-- Loan Disbursement Record --}}
        <a href="{{ route('loan.disbursement-register') }}" 
           class="group flex items-center gap-3 bg-surface p-3 rounded-lg border border-border-main hover:border-emerald-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/20 rounded flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">payments</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">Loan Disbursement</h3>
                <span class="text-[9px] text-emerald-600 dark:text-emerald-500 font-black uppercase tracking-wider">Active Register</span>
            </div>
        </a>

        {{-- Cash Book --}}
        <a href="{{ route('cashbook') }}" 
           class="group flex items-center gap-3 bg-surface p-3 rounded-lg border border-border-main hover:border-amber-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/20 rounded flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">Cash Book</h3>
                <span class="text-[9px] text-amber-600 dark:text-amber-500 font-black uppercase tracking-wider tracking-wider">Financial Ledger</span>
            </div>
        </a>

        {{-- Daily Savings Record --}}
        <a href="{{ route('daily-savings.record') }}" 
           class="group flex items-center gap-3 bg-surface p-3 rounded-lg border border-border-main hover:border-blue-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/20 rounded flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">event_repeat</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">Daily Savings</h3>
                <span class="text-[9px] text-blue-600 dark:text-blue-500 font-black uppercase tracking-wider">High Frequency</span>
            </div>
        </a>

        {{-- Repayment/Savings Record --}}
        <a href="{{ route('ledger.dashboard') }}" 
           class="group flex items-center gap-3 bg-surface p-3 rounded-lg border border-border-main hover:border-indigo-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/20 rounded flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">receipt_long</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">Repayment/Savings</h3>
                <span class="text-[9px] text-indigo-600 dark:text-indigo-500 font-black uppercase tracking-wider">Collection Ledger</span>
            </div>
        </a>

        {{-- Savings Withdrawal --}}
        <a href="{{ route('savings.withdrawals') }}" 
           class="group flex items-center gap-3 bg-surface p-3 rounded-lg border border-border-main hover:border-rose-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-rose-50 dark:bg-rose-900/20 rounded flex items-center justify-center text-rose-600 dark:text-rose-400 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">outbox</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">Savings Withdrawal</h3>
                <span class="text-[9px] text-rose-600 dark:text-rose-500 font-black uppercase tracking-widest">Active Register</span>
            </div>
        </a>

        {{-- Transactions Hub --}}
        <a href="{{ route('transactions') }}" 
           class="group flex items-center gap-3 bg-surface p-3 rounded-lg border border-border-main hover:border-primary/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-primary/5 dark:bg-primary/20 rounded flex items-center justify-center text-primary dark:text-primary-light group-hover:bg-primary group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">receipt_long</span>
            </div>
            <div class="flex flex-col">
                <h3 class="text-xs font-bold text-slate-800 dark:text-white">Transaction History</h3>
                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-wider">Master Ledger</span>
            </div>
        </a>
    </div>
</div>

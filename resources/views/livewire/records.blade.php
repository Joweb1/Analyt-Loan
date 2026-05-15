<div class="w-full py-8 px-2">
    <div class="mb-8 px-2">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Records Hub</h1>
        <p class="mt-1 text-xs text-slate-500 font-medium tracking-wide">Select a digital record book to view or manage entries.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        {{-- Loan Disbursement Record --}}
        <a href="{{ route('loan.disbursement-register') }}" 
           class="group flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200 hover:border-emerald-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-emerald-50 rounded flex items-center justify-center text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">payments</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800">Loan Disbursement</h3>
                <span class="text-[9px] text-emerald-600 font-black uppercase tracking-wider">Active Register</span>
            </div>
        </a>

        {{-- Cash Book --}}
        <a href="{{ route('cashbook') }}" 
           class="group flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200 hover:border-amber-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-amber-50 rounded flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">account_balance_wallet</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800">Cash Book</h3>
                <span class="text-[9px] text-amber-600 font-black uppercase tracking-wider tracking-wider">Financial Ledger</span>
            </div>
        </a>

        {{-- Daily Savings Record --}}
        <a href="{{ route('daily-savings.record') }}" 
           class="group flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200 hover:border-blue-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-blue-50 rounded flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">event_repeat</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800">Daily Savings</h3>
                <span class="text-[9px] text-blue-600 font-black uppercase tracking-wider">High Frequency</span>
            </div>
        </a>

        {{-- Repayment/Savings Record --}}
        <a href="{{ route('ledger.dashboard') }}" 
           class="group flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200 hover:border-indigo-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-indigo-50 rounded flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">receipt_long</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800">Repayment/Savings</h3>
                <span class="text-[9px] text-indigo-600 font-black uppercase tracking-wider">Collection Ledger</span>
            </div>
        </a>

        {{-- Savings Withdrawal --}}
        <a href="{{ route('savings.withdrawals') }}" 
           class="group flex items-center gap-3 bg-white p-3 rounded-lg border border-slate-200 hover:border-rose-500/50 hover:shadow-md transition-all duration-200">
            <div class="w-10 h-10 bg-rose-50 rounded flex items-center justify-center text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-200">
                <span class="material-symbols-outlined text-xl">outbox</span>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-800">Savings Withdrawal</h3>
                <span class="text-[9px] text-rose-600 font-black uppercase tracking-wider">Active Register</span>
            </div>
        </a>
    </div>
</div>

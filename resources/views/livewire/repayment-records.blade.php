<div class="-mx-6 md:mx-0 max-w-6xl md:mx-auto space-y-4 md:space-y-6 p-0 pb-10 px-4 md:px-0">
    <!-- Header Section -->
    <div class="flex flex-col gap-4 pt-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3 md:gap-4">
             <a href="{{ route('collections') }}" class="size-11 flex items-center justify-center bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-100 dark:border-slate-800 text-slate-500 hover:text-primary transition-colors shrink-0 shadow-sm">
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">
                    <span>Collections</span>
                    <span class="material-symbols-outlined text-[10px]">chevron_right</span>
                    <span class="text-slate-800 dark:text-white">History</span>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate">Repayment Records</h2>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Compact Stats Card -->
            <div class="flex items-center gap-4 bg-primary text-white px-5 py-3 rounded-2xl shadow-lg shadow-primary/20">
                <div class="size-9 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-sm">payments</span>
                </div>
                <div class="min-w-0">
                    <p class="text-[9px] font-black uppercase tracking-widest opacity-70 leading-none mb-1">Total Collected</p>
                    <p class="text-sm md:text-base font-black tracking-tight truncate">₦{{ number_format($totalAmount, 2) }}</p>
                </div>
                <div class="h-6 w-px bg-white/10 mx-1"></div>
                <div class="text-[10px] font-black uppercase opacity-70 shrink-0">{{ $totalCount }}</div>
            </div>

            @can('export_and_print')
                <button wire:click="export" class="flex items-center justify-center gap-2 px-5 py-3 bg-white dark:bg-[#1a1f2b] border-2 border-slate-900 dark:border-blue-900 text-slate-900 dark:text-white rounded-2xl text-[10px] font-black shadow-sm hover:bg-slate-900 hover:text-white dark:hover:bg-blue-900 transition-all uppercase tracking-widest h-[52px]">
                    <span class="material-symbols-outlined text-sm">download</span>
                    <span>Export CSV</span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Search & Filters Bar -->
    <div class="flex flex-col gap-3 md:flex-row md:items-center">
        <div class="relative group w-full flex-1">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400 text-xl group-focus-within:text-primary transition-colors">search</span>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search Borrower or Loan ID..." class="w-full pl-12 pr-4 py-4 bg-white dark:bg-[#1a1f2b] border-none rounded-2xl shadow-sm focus:ring-2 focus:ring-primary/20 transition-all text-sm font-bold placeholder-slate-400 h-[52px]">
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto shrink-0">
            <div class="relative flex-1 md:w-48">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 text-lg">calendar_month</span>
                </span>
                <select wire:model.live="dateRange" class="w-full pl-10 pr-10 py-4 bg-white dark:bg-[#1a1f2b] border-none rounded-2xl shadow-sm text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer appearance-none h-[52px]">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined">expand_more</span>
                </span>
            </div>

            @if($search || $dateRange !== 'all')
                <button wire:click="$set('dateRange', 'all'); $set('search', '')" class="size-[52px] flex items-center justify-center bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-100 transition-colors shrink-0 shadow-sm" title="Clear Filters">
                    <span class="material-symbols-outlined">filter_alt_off</span>
                </button>
            @endif
        </div>

        @if($dateRange === 'custom')
            <div class="flex items-center gap-2 animate-in fade-in zoom-in-95 duration-200 w-full md:w-auto">
                <input wire:model.live="customStartDate" type="date" class="flex-1 md:w-36 px-3 py-4 bg-white dark:bg-[#1a1f2b] border-none rounded-2xl shadow-sm text-[10px] font-bold focus:ring-2 focus:ring-primary/20 h-[52px]">
                <span class="text-slate-400 font-bold text-[10px] uppercase">To</span>
                <input wire:model.live="customEndDate" type="date" class="flex-1 md:w-36 px-3 py-4 bg-white dark:bg-[#1a1f2b] border-none rounded-2xl shadow-sm text-[10px] font-bold focus:ring-2 focus:ring-primary/20 h-[52px]">
            </div>
        @endif
    </div>

    <!-- Main List Section -->
    <div class="px-0">
        <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-5">Borrower / Loan</th>
                            <th class="px-6 py-5">Amount / Method</th>
                            <th class="px-6 py-5">Recorded By</th>
                            <th class="px-6 py-5">Payment Date</th>
                            <th class="px-6 py-5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($repayments as $repayment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-9 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[10px] font-black uppercase">
                                            {{ substr($repayment->loan->borrower->user->name, 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $repayment->loan->borrower->user->name }}</p>
                                            <p class="text-[10px] font-mono text-slate-400 font-bold uppercase tracking-tighter truncate">{{ $repayment->loan->loan_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-green-600">₦{{ number_format($repayment->amount, 2) }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $repayment->payment_method }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                        <span class="material-symbols-outlined text-sm opacity-50">person</span>
                                        <span class="text-xs font-bold truncate max-w-[120px]">{{ $repayment->collector->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $repayment->paid_at->format('M d, Y') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('loan.show', $repayment->loan_id) }}" class="inline-flex items-center justify-center size-9 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-primary transition-all">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-40">
                                        <span class="material-symbols-outlined text-6xl mb-4">history</span>
                                        <p class="text-lg font-bold">No repayment records found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($repayments->hasPages())
                <div class="px-6 py-4 border-t border-slate-50 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/20">
                    {{ $repayments->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

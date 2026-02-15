<div class="max-w-6xl mx-auto space-y-8 p-0">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('collections') }}" class="hover:text-primary transition-colors">Collections</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Repayment Records</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Repayment History</h2>
        </div>
        <div class="flex gap-3">
             <button wire:click="export" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                <span class="material-symbols-outlined text-lg">download</span>
                Export CSV
            </button>
        </div>
    </div>

    <!-- Stats & Filters Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 px-1 lg:px-2">
        <!-- Stats Card -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-primary text-white p-6 rounded-3xl shadow-xl shadow-primary/20 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60 mb-1">Total Collected</p>
                    <h3 class="text-3xl font-black tracking-tighter">₦{{ number_format($totalAmount, 2) }}</h3>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="bg-white/20 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">{{ $totalCount }} Payments</span>
                    </div>
                </div>
                <!-- Abstract patterns -->
                <div class="absolute -right-4 -bottom-4 size-24 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -left-8 -top-8 size-32 bg-blue-400/10 rounded-full blur-3xl"></div>
            </div>

            <!-- Advanced Filter Card -->
            <div class="bg-white dark:bg-[#1a1f2b] p-6 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm">
                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-lg">filter_alt</span>
                    Advanced Filter
                </h4>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 px-1">Period</label>
                        <select wire:model.live="dateRange" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    @if($dateRange === 'custom')
                        <div class="space-y-4 animate-in fade-in slide-in-from-top-2 duration-200">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 px-1">Start Date</label>
                                <input wire:model.live="customStartDate" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 px-1">End Date</label>
                                <input wire:model.live="customEndDate" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
                            </div>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-slate-50 dark:border-slate-800">
                        <button wire:click="$set('dateRange', 'all'); $set('search', '')" class="w-full py-2.5 text-[10px] font-black uppercase text-slate-400 hover:text-primary transition-colors tracking-widest">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Search Bar -->
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 text-lg group-focus-within:text-primary transition-colors">search</span>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by Borrower Name or Loan ID..." class="w-full pl-12 pr-4 py-4 bg-white dark:bg-[#1a1f2b] border-none rounded-2xl shadow-sm focus:ring-2 focus:ring-primary/20 transition-all text-sm font-bold placeholder-slate-400">
            </div>

            <!-- List -->
            <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                                <th class="px-6 py-4">Borrower / Loan</th>
                                <th class="px-6 py-4">Amount / Method</th>
                                <th class="px-6 py-4">Recorded By</th>
                                <th class="px-6 py-4">Payment Date</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                            @forelse($repayments as $repayment)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[10px] font-black">
                                                {{ substr($repayment->loan->borrower->user->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-900 dark:text-white">{{ $repayment->loan->borrower->user->name }}</p>
                                                <p class="text-[10px] font-mono text-slate-400 font-bold uppercase tracking-tighter">{{ $repayment->loan->loan_number }}</p>
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
                                        <div class="flex items-center gap-2">
                                            <div class="size-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-xs text-slate-400">person</span>
                                            </div>
                                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $repayment->collector->name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $repayment->paid_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('loan.show', $repayment->loan_id) }}" class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-primary transition-all">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-20 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <span class="material-symbols-outlined text-5xl mb-4 opacity-20">history</span>
                                            <p class="text-lg font-bold">No repayment records found</p>
                                            <p class="text-sm">Try adjusting your filters or search terms.</p>
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
</div>

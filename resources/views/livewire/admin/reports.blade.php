<div class="flex flex-col gap-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Platform Intelligence</h2>
            <p class="text-slate-500 dark:text-slate-400">Deep dive into organization performance and platform growth.</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-primary p-8 rounded-3xl shadow-xl shadow-primary/20 text-white relative overflow-hidden">
            <div class="relative z-10">
                <p class="text-sm font-medium opacity-80 uppercase tracking-wider mb-2">Total Platform Lending</p>
                <h3 class="text-3xl font-black">₦{{ number_format($totals['lent'], 0) }}</h3>
                <p class="mt-4 text-xs opacity-60">Cumulative volume from all registered lenders</p>
            </div>
            <span class="material-symbols-outlined absolute -bottom-4 -right-4 text-9xl opacity-10 rotate-12">account_balance</span>
        </div>

        <div class="bg-white dark:bg-[#1a1f2b] p-8 rounded-3xl shadow-soft border border-slate-100 dark:border-slate-800">
            <p class="text-sm font-medium text-slate-400 uppercase tracking-wider mb-2">Total Collection</p>
            <h3 class="text-3xl font-black text-slate-800 dark:text-white">₦{{ number_format($totals['collected'], 0) }}</h3>
            <div class="mt-4 flex items-center gap-2">
                <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500" style="width: {{ $totals['lent'] > 0 ? ($totals['collected'] / $totals['lent']) * 100 : 0 }}%"></div>
                </div>
                <span class="text-xs font-bold text-slate-500">{{ $totals['lent'] > 0 ? round(($totals['collected'] / $totals['lent']) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1f2b] p-8 rounded-3xl shadow-soft border border-slate-100 dark:border-slate-800">
            <p class="text-sm font-medium text-slate-400 uppercase tracking-wider mb-2">Platform Entities</p>
            <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $totals['organizations'] }}</h3>
            <p class="mt-4 text-xs text-slate-400">Active organizations managing loans</p>
        </div>
    </div>

    <!-- Organization Performance Table -->
    <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl shadow-soft border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">Lender Performance League</h3>
            <button class="flex items-center gap-2 text-xs font-bold text-primary px-4 py-2 bg-primary/5 rounded-xl hover:bg-primary/10 transition-all">
                <span class="material-symbols-outlined text-sm">download</span>
                Export Report
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                        <th class="px-8 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Lender Organization</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Volume Lent</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Volume Collected</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Recovery Rate</th>
                        <th class="px-8 py-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Scale</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($orgStats->sortByDesc('total_lent') as $stat)
                        <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/20 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center border border-slate-100 dark:border-slate-700">
                                        @if($stat->logo_path)
                                            <img src="{{ Storage::url($stat->logo_path) }}" class="w-full h-full object-contain rounded-xl">
                                        @else
                                            <span class="material-symbols-outlined text-slate-400">business</span>
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">{{ $stat->name }}</h4>
                                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">{{ $stat->rc_number ?? 'RC: N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-bold text-slate-700 dark:text-slate-300">
                                ₦{{ number_format($stat->total_lent, 0) }}
                            </td>
                            <td class="px-8 py-6 font-bold text-emerald-500">
                                ₦{{ number_format($stat->total_collected, 0) }}
                            </td>
                            <td class="px-8 py-6">
                                @php
                                    $rate = $stat->total_lent > 0 ? ($stat->total_collected / $stat->total_lent) * 100 : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold {{ $rate >= 80 ? 'text-emerald-500' : ($rate >= 50 ? 'text-amber-500' : 'text-rose-500') }}">
                                        {{ round($rate, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg">
                                        {{ $stat->borrowers_count }} CUSTOMERS
                                    </span>
                                    <span class="text-[10px] font-bold text-primary bg-primary/5 px-2 py-1 rounded-lg uppercase tracking-widest text-center">
                                        {{ $stat->staff_count }} STAFF
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

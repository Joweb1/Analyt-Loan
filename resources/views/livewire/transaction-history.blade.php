<div class="w-full mx-auto space-y-8 p-0 relative">
    {{-- Fixed Back Button --}}
    <button onclick="window.history.back()" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-slate-200 dark:border-white/20 rounded-full text-slate-900 dark:text-white hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </button>

    <div class="px-2 pt-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Transaction History</h1>
                <p class="text-sm text-slate-500 font-medium mt-1 uppercase tracking-widest opacity-70">Master Financial Ledger</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="window.print()" class="px-6 py-3 bg-surface border border-border-main text-slate-700 dark:text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">print</span>
                    Print Ledger
                </button>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-surface rounded-2xl shadow-sm border border-border-main p-6 mb-8">
            <div class="flex flex-wrap items-center gap-6">
                {{-- Period Selector --}}
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">Select Period</label>
                    <select wire:model.live="period" class="bg-background-light dark:bg-zinc-800 border-none rounded-xl text-xs font-black px-5 py-3.5 outline-none focus:ring-4 focus:ring-primary/10 transition-all min-w-[180px] text-primary dark:text-white">
                        <option value="today">Today</option>
                        <option value="this_week">This Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                        <option value="last_year">Last Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                {{-- Type Selector --}}
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">Transaction Type</label>
                    <select wire:model.live="type" class="bg-background-light dark:bg-zinc-800 border-none rounded-xl text-xs font-black px-5 py-3.5 outline-none focus:ring-4 focus:ring-primary/10 transition-all min-w-[180px] text-primary dark:text-white">
                        @foreach($types as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if($period === 'custom')
                    <div class="flex flex-col gap-2 animate-in fade-in slide-in-from-left-4">
                        <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest px-1">Date Range</label>
                        <div class="flex items-center gap-2">
                            <input type="date" wire:model.live="customStart" class="bg-background-light dark:bg-zinc-800 border-none rounded-xl text-xs font-black px-4 py-3 focus:ring-4 focus:ring-primary/10 transition-all text-primary dark:text-white">
                            <span class="text-slate-300 dark:text-slate-600">to</span>
                            <input type="date" wire:model.live="customEnd" class="bg-background-light dark:bg-zinc-800 border-none rounded-xl text-xs font-black px-4 py-3 focus:ring-4 focus:ring-primary/10 transition-all text-primary dark:text-white">
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Transactions Table --}}
        <div class="bg-surface rounded-2xl border border-border-main shadow-sm overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm print:text-[10px]">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-border-main">
                        <tr>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date & Ref</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Type</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer / Participant</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Method</th>
                            <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @forelse($transactions as $trx)
                            @php
                                $typeDirection = in_array($trx->type, ['deposit', 'daily_thrift', 'repayment', 'registration_fee', 'interest', 'penalty', 'charge', 'balance_update', 'budget_update']) ? 1 : -1;
                                $effectiveSign = $trx->amount->getMinorAmount() * $typeDirection;
                                $isPositive = $effectiveSign > 0;
                                $isNegative = $effectiveSign < 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ fetch_data($trx?->transaction_date?->format('d M, Y') ?? null) }}</p>
                                    <p class="text-[9px] font-mono text-slate-400 uppercase mt-0.5">{{ fetch_data($trx?->reference ?? null) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black uppercase tracking-tight {{ $isPositive ? 'text-emerald-600' : ($isNegative ? 'text-rose-600' : 'text-slate-500') }}">
                                            {{ fetch_data(str_replace('_', ' ', $trx?->type) ?? null) }}
                                        </span>
                                        @if($trx->notes)
                                            <span class="text-[9px] text-slate-400 italic mt-0.5">{{ fetch_data($trx?->notes ?? null) }}</span>
                                        @endif
                                        @if($trx->parent_id)
                                            <div class="flex items-center gap-1 mt-1 opacity-60">
                                                <span class="material-symbols-outlined text-[10px]">link</span>
                                                <span class="text-[9px] font-mono uppercase tracking-tighter">REF: {{ fetch_data($trx?->parent?->reference ?? 'ORIGINAL' ?? null) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($trx->user)
                                        <div class="flex items-center gap-3">
                                            <div class="size-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                                <span class="material-symbols-outlined text-sm">person</span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800 dark:text-white text-xs leading-none">{{ fetch_data($trx?->user?->name ?? null) }}</p>
                                                <p class="text-[9px] text-slate-400 mt-1 uppercase tracking-tighter">{{ fetch_data($trx?->user?->getRoleNames()?->first() ?? 'Client' ?? null) }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">System Internal</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[9px] font-black uppercase">
                                        {{ fetch_data($trx?->payment_method ?? 'system' ?? null) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-black {{ $isPositive ? 'text-emerald-600' : ($isNegative ? 'text-rose-600' : 'text-slate-900 dark:text-white') }}">
                                        {{ $isPositive ? '+' : ($isNegative ? '-' : '') }}₦{{ fetch_data($trx?->amount?->absolute()->format() ?? null) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <span class="material-symbols-outlined text-6xl">receipt_long</span>
                                        <p class="font-bold mt-2">No transactions found for this period.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-6 py-5 bg-slate-50/50 dark:bg-slate-800/20 border-t border-border-main">
                    {{ fetch_data($transactions?->links() ?? null) }}
                </div>
            @endif
        </div>
    </div>
    
    <style>
        @media print {
            .fixed, button, select, label, .mb-12, .mb-8, .p-6 {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
            }
            .bg-white {
                border: none !important;
                box-shadow: none !important;
            }
            h1 {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border-bottom: 1px solid #eee !important;
                padding: 8px !important;
            }
            .text-emerald-600, .text-rose-600 {
                color: black !important;
                font-weight: bold !important;
            }
        }
    </style>
</div>

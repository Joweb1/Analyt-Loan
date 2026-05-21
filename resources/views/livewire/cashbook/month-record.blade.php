<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 md:px-8">
    {{-- Fixed Back Button --}}
    <a href="{{ route('cashbook', ['date' => $dateString]) }}" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-white/20 rounded-full text-slate-900 hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Daily Book</span>
    </a>

    {{-- Header Section --}}
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $currentMonthName }} Audit Ledger</h1>
                <p class="mt-1 text-sm text-gray-500 font-medium">Consolidated weekly view of vault performance and budget compliance.</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="bg-white px-4 py-2 border border-gray-200 rounded-sm shadow-sm">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Month Budget</span>
                    <span class="text-lg font-black text-gray-900">{{ $totalBudget->format() }}</span>
                </div>
                <div class="flex items-center bg-white border border-gray-200 rounded-sm shadow-sm pr-2">
                    <div class="px-4 py-2 border-r border-gray-100 {{ $remainingBudget->isNegative() ? 'text-rose-600' : 'text-amber-600' }}">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Remaining</span>
                        <span class="text-lg font-black">{{ $remainingBudget->format() }}</span>
                    </div>
                    <button wire:click="openBudgetModal" 
                        class="ml-2 p-2 text-blue-600 hover:bg-blue-50 rounded-sm transition-colors group {{ !auth()->user()->isAdmin() ? 'opacity-40 pointer-events-none' : '' }}" 
                        title="Set month budget">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Summary Area --}}
    <div class="max-w-7xl mx-auto grid grid-cols-2 gap-4 md:grid-cols-5 mb-10">
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-sm p-5 transition hover:shadow-md">
            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Inflow</dt>
            <dd class="text-2xl font-black text-emerald-600">+{{ $stats['total_inflow']->format() }}</dd>
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-sm p-5 transition hover:shadow-md">
            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Outflow</dt>
            <dd class="text-2xl font-black text-rose-600">-{{ $stats['total_outflow']->format() }}</dd>
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-sm p-5 transition hover:shadow-md">
            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Live Bank Balance</dt>
            <dd class="text-2xl font-black text-blue-600">{{ $liveBalance->format() }}</dd>
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-sm p-5 transition hover:shadow-md group relative">
            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Initial Balance</dt>
            <dd class="text-2xl font-black text-gray-900">{{ $openingBalance->format() }}</dd>
            @if(auth()->user()->isAdmin())
                <button wire:click="openBalanceModal" class="absolute top-2 right-2 p-1 text-gray-300 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                </button>
            @endif
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded-sm p-5 transition hover:shadow-md border-l-4 border-l-blue-500">
            <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Audit Status</dt>
            <dd class="text-2xl font-black text-blue-600">{{ $stats['verified_count'] }}/{{ $stats['days_count'] }} Days</dd>
        </div>
    </div>

    {{-- Weekly Ledger Sections --}}
    <div class="max-w-7xl mx-auto space-y-12">
        @foreach($groupedEntries as $week => $entries)
            <div x-data="{ open: true }">
                <div class="flex items-center justify-between mb-6 cursor-pointer" @click="open = !open">
                    <h2 class="text-xl font-semibold text-gray-400 tracking-wide uppercase flex items-center">
                        <span class="mr-3">{{ $week }}</span>
                        <span class="h-px w-24 bg-gray-200"></span>
                    </h2>
                    <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-1 rounded-sm">{{ $entries->count() }} Records</span>
                </div>

                <div x-show="open" x-collapse class="space-y-4">
                    @foreach($entries as $entry)
                        <div class="bg-white border border-gray-200 rounded-sm shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                            <div class="p-5 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex items-center space-x-6">
                                    <div class="flex-shrink-0 text-center border-r border-gray-100 pr-6">
                                        <span class="block text-2xl font-black text-gray-900">{{ $entry->entry_date->format('d') }}</span>
                                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $entry->entry_date->format('D') }}</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-3">
                                            <h3 class="text-lg font-bold text-gray-900">{{ $entry->entry_date->format('l, jS F') }}</h3>
                                            @php
                                                $statusClasses = [
                                                    'verified' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                    'discrepancy' => 'bg-rose-50 text-rose-700 border-rose-100',
                                                ];
                                                $currentClass = $statusClasses[$entry->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                            @endphp
                                            <span class="px-3 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $currentClass }}">
                                                {{ $entry->status }}
                                            </span>
                                        </div>
                                        <div class="flex items-center mt-1 space-x-4">
                                            <span class="text-[11px] text-gray-500 font-medium">Cash in hand: <span class="font-black text-gray-900">{{ $entry->actual_cash_at_hand->format() }}</span></span>
                                            <span class="text-[11px] text-gray-500 font-medium">Expenses: <span class="font-black text-rose-600">{{ $entry->daily_expense_amount->format() }}</span></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between md:justify-end gap-12">
                                    <div class="text-right">
                                        <span class="block text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1">Total Bank Deposit</span>
                                        <span class="text-xl font-black text-blue-600">
                                            {{ $entry->bank_deposit_amount->format() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <a href="{{ route('cashbook', ['date' => $entry->entry_date->toDateString()]) }}" 
                                            class="p-2 text-gray-400 hover:text-blue-600 transition-colors bg-gray-50 rounded-sm border border-gray-200"
                                            title="View Daily Record">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Quick Discrepancy Insight if not balanced --}}
                            @if(!$entry->actual_cash_at_hand->equals($entry->expected_cash_at_hand) || $entry->shortfall_report)
                                <div class="px-5 py-2 bg-rose-50/50 border-t border-rose-100 flex flex-col space-y-1">
                                    @if(!$entry->actual_cash_at_hand->equals($entry->expected_cash_at_hand))
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                                            <span class="text-[10px] font-bold text-rose-700 uppercase">Variance: {{ $entry->actual_cash_at_hand->subtract($entry->expected_cash_at_hand)->format() }}</span>
                                        </div>
                                    @endif
                                    @if($entry->shortfall_report)
                                        <div class="pl-7">
                                            <p class="text-[10px] text-rose-600 italic font-medium">Report: "{{ $entry->shortfall_report }}"</p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Budget Modal --}}
    @if($showBudgetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-md">
            <div class="bg-white rounded-sm shadow-2xl max-w-md w-full overflow-hidden border border-gray-200 transform transition-all animate-in fade-in zoom-in duration-300">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter">Set Month Budget</h3>
                        <button wire:click="$set('showBudgetModal', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Expense Budget Amount ({{ config('app.currency', 'NGN') }})</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-black">$</span>
                                </div>
                                <input type="number" step="0.01" wire:model="newBudgetAmount"
                                    class="w-full pl-10 pr-4 py-4 rounded-sm border-gray-200 bg-gray-50 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 text-2xl font-black text-gray-900 transition-all">
                            </div>
                            <p class="mt-3 text-[10px] text-gray-400 font-medium italic">This amount will be used to fund all daily expenses for {{ $currentMonthName }}.</p>
                        </div>

                        <div class="pt-4 flex flex-col space-y-3">
                            <button wire:click="saveBudget" class="w-full py-4 bg-blue-600 text-white rounded-sm text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all transform hover:-translate-y-1 active:translate-y-0">Save & Update Ledger</button>
                            <button wire:click="$set('showBudgetModal', false)" class="w-full py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Initial Balance Modal --}}
    @if($showBalanceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-md">
            <div class="bg-white rounded-sm shadow-2xl max-w-md w-full overflow-hidden border border-gray-200 transform transition-all animate-in fade-in zoom-in duration-300">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tighter">Set Initial Balance</h3>
                        <button wire:click="$set('showBalanceModal', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Month Opening Balance Snapshot ({{ config('app.currency', 'NGN') }})</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-black">$</span>
                                </div>
                                <input type="number" step="0.01" wire:model="newOpeningBalanceAmount"
                                    class="w-full pl-10 pr-4 py-4 rounded-sm border-gray-200 bg-gray-50 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 text-2xl font-black text-gray-900 transition-all">
                            </div>
                            <p class="mt-3 text-[10px] text-gray-400 font-medium italic">This snapshot represents the actual bank balance on the 1st of {{ $currentMonthName }}.</p>
                        </div>

                        <div class="pt-4 flex flex-col space-y-3">
                            <button wire:click="saveBalance" class="w-full py-4 bg-blue-600 text-white rounded-sm text-xs font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all transform hover:-translate-y-1 active:translate-y-0">Save Snapshot</button>
                            <button wire:click="$set('showBalanceModal', false)" class="w-full py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Notification Toast (Minimal) --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-5 right-5 z-50">
        <div class="bg-white border shadow-xl rounded-sm p-4 flex items-center space-x-3" 
            :class="type === 'success' ? 'border-emerald-100' : 'border-rose-100'">
            <div :class="type === 'success' ? 'text-emerald-500' : 'text-rose-500'">
                <template x-if="type === 'success'">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </div>
            <p class="text-sm font-bold text-gray-800" x-text="message"></p>
        </div>
    </div>
</div>

<div class="min-h-screen bg-background-light py-8 px-4 sm:px-6 lg:px-8">
    {{-- Fixed Back Button --}}
    <a href="{{ route('records') }}" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 dark:bg-slate-800/30 backdrop-blur-md border border-white/20 dark:border-slate-700/50 rounded-full text-primary dark:text-white hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </a>

    {{-- Header Section --}}
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-primary dark:text-white tracking-tight">Savings Withdrawal Ledger</h1>
                    <p class="hidden sm:block mt-1 text-sm text-slate-500 dark:text-slate-400">Premium digital records for microfinance savings and loan adjustments.</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 items-stretch sm:items-center">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1 sm:flex-none">
                        <input type="date" wire:model.live="selectedDate" 
                            class="block w-full pl-3 pr-10 py-2 border border-border-main rounded-lg text-sm font-black focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out bg-surface text-primary dark:text-white">
                    </div>
                    <button wire:click="showAll" 
                        title="View All Records"
                        class="p-2 bg-surface border border-border-main rounded-lg text-slate-500 hover:text-blue-600 hover:border-blue-200 dark:hover:border-blue-900/50 transition-all shadow-sm flex items-center justify-center {{ is_null($selectedDate) ? 'text-blue-600 border-blue-200 bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <span class="material-symbols-outlined text-xl">calendar_view_month</span>
                    </button>
                </div>
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Search customer or Ref #..." 
                        class="block w-full pl-10 pr-3 py-2 border border-border-main rounded-lg leading-5 bg-surface text-primary dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
                <select wire:model.live="status" class="block w-full pl-3 pr-10 py-2 text-base border-border-main focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg bg-surface text-primary dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="verified">Verified</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Stats Summary Area --}}
    <div class="max-w-7xl mx-auto flex flex-wrap gap-3 mb-10">
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Withdrawals</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-rose-600 dark:text-rose-500">{{ $stats['total_withdrawals'] }}</dd>
        </div>
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Regular Bal.</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-emerald-600 dark:text-emerald-500">{{ $stats['total_savings_balance'] }}</dd>
        </div>
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Daily Thrift</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-blue-600 dark:text-blue-400">{{ $stats['total_thrift_balance'] }}</dd>
        </div>
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Loan Adjusts</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-slate-700 dark:text-slate-300">{{ $stats['total_loan_adjustments'] }}</dd>
        </div>
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Approved</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-primary dark:text-white">{{ $stats['approved_count'] }}</dd>
        </div>
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Pending</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-amber-500 dark:text-amber-400">{{ $stats['pending_count'] }}</dd>
        </div>
        <div class="flex-1 min-w-[140px] bg-surface overflow-hidden shadow-sm border border-border-main rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-slate-400 dark:text-slate-500 truncate uppercase tracking-widest">Daily Flow</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-primary dark:text-white">{{ $stats['daily_flow'] }}</dd>
        </div>
    </div>

    {{-- Ledger Sections --}}
    <div class="max-w-7xl mx-auto space-y-12">
        @forelse($groupedRecords as $month => $records)
            <div x-data="{ open: true }">
                <div class="flex items-center justify-between mb-6 cursor-pointer" @click="open = !open">
                    <h2 class="text-xl font-semibold text-slate-400 dark:text-slate-600 tracking-wide uppercase flex items-center">
                        <span class="mr-3">{{ $month }}</span>
                        <span class="h-px w-24 bg-border-main"></span>
                    </h2>
                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500 bg-background-light px-2 py-1 rounded-md">{{ fetch_data($records?->count() ?? null) }} Records</span>
                </div>

                <div x-show="open" x-collapse class="space-y-4">
                    @foreach($records as $index => $record)
                        <div x-data="{ expanded: false }" 
                            class="bg-surface border border-border-main rounded shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                            
                            {{-- Card Header --}}
                            <div @click="expanded = !expanded" class="p-5 cursor-pointer flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-background-light text-slate-400 dark:text-slate-500 text-xs font-medium border border-border-main">
                                            {{ fetch_data(str_pad($loop?->iteration, 2, '0', STR_PAD_LEFT) ?? null) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-xl font-bold text-primary dark:text-white">{{ fetch_data($record?->savingsAccount?->user?->name ?? 'N/A' ?? null) }}</h3>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono bg-background-light text-slate-500 dark:text-slate-400 uppercase">{{ fetch_data($record?->savingsAccount?->user?->borrower?->custom_id ?? 'N/A' ?? null) }}</span>
                                        </div>
                                        <div class="flex items-center mt-1 space-x-3">
                                            <span class="flex items-center text-xs font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-full">
                                                Balance: {{ fetch_data($record?->snapshot_balance?->format() ?? null) }}
                                            </span>
                                            <span class="text-xs text-slate-400 dark:text-slate-500 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ fetch_data($record?->transaction_date?->format('d M, Y') ?? null) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between lg:justify-end gap-6">
                                    <div class="text-right">
                                        <span class="block text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-widest mb-1">Withdrawn Amount</span>
                                        <span class="text-2xl font-black text-rose-600 dark:text-rose-500">{{ fetch_data($record?->amount_withdrawn?->format() ?? null) }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @php
                                            $statusClasses = [
                                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-900/30',
                                                'pending' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-900/30',
                                                'verified' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-900/30',
                                                'rejected' => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-900/30',
                                                'processing' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-900/30',
                                            ];
                                            $currentClass = $statusClasses[$record->status] ?? 'bg-background-light text-slate-700 dark:text-slate-300 border-border-main';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $currentClass }}">
                                            {{ fetch_data($record?->status ?? null) }}
                                        </span>
                                        <svg class="w-5 h-5 text-slate-300 dark:text-slate-700 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div x-show="expanded" x-collapse class="border-t border-border-main bg-background-light/30">
                                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-8">
                                    {{-- Details --}}
                                    <div class="space-y-6">
                                        <div>
                                            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Transaction Details</span>
                                            <div class="space-y-2">
                                                <div class="flex justify-between items-center bg-surface p-2 rounded border border-border-main shadow-sm">
                                                    <span class="text-xs text-slate-500">Ref Number</span>
                                                    <span class="text-xs font-mono font-bold text-primary dark:text-white">{{ fetch_data($record?->reference ?? null) }}</span>
                                                </div>
                                                <div class="flex justify-between items-center bg-surface p-2 rounded border border-blue-100 dark:border-blue-900/30 shadow-sm">
                                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-bold">Loan Adjustment</span>
                                                    <span class="text-xs font-bold text-blue-700 dark:text-blue-300">{{ fetch_data($record?->loan_adjustment_amount?->format() ?? null) }}</span>
                                                </div>
                                                <div class="flex justify-between items-center bg-surface p-2 rounded border border-border-main shadow-sm">
                                                    <span class="text-xs text-slate-500">Processed By</span>
                                                    <span class="text-xs font-bold text-primary dark:text-white">{{ fetch_data($record?->staff?->name ?? 'System' ?? null) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if(auth()->user()->hasRole('Admin'))
                                        <div>
                                            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Workflow Actions</span>
                                            <div class="flex flex-wrap gap-2">
                                                @if($record->status === 'pending')
                                                    <button wire:click="updateStatus('{{ fetch_data($record?->id ?? null) }}', 'verified')" class="px-3 py-1 bg-blue-600 text-white text-[10px] font-bold uppercase rounded-md shadow hover:bg-blue-700 transition">Verify</button>
                                                @endif
                                                @if(in_array($record->status, ['pending', 'verified', 'processing']))
                                                    <button wire:click="updateStatus('{{ fetch_data($record?->id ?? null) }}', 'approved')" class="px-3 py-1 bg-emerald-600 text-white text-[10px] font-bold uppercase rounded-md shadow hover:bg-emerald-700 transition">Approve</button>
                                                    <button wire:click="updateStatus('{{ fetch_data($record?->id ?? null) }}', 'rejected')" class="px-3 py-1 bg-rose-600 text-white text-[10px] font-bold uppercase rounded-md shadow hover:bg-rose-700 transition">Reject</button>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Notes --}}
                                    <div class="md:col-span-2">
                                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Withdrawal Notes & Observations</span>
                                        <div class="relative">
                                            <textarea 
                                                wire:change="updateNote('{{ fetch_data($record?->id ?? null) }}', $event.target.value)"
                                                placeholder="Add internal notes about this withdrawal or loan adjustment..."
                                                class="block w-full rounded border border-border-main bg-surface text-primary dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm min-h-[120px] resize-none p-4"
                                            >{{ fetch_data($record?->notes ?? null) }}</textarea>
                                            <div class="absolute bottom-3 right-3 opacity-20">
                                                <svg class="w-8 h-8 text-slate-400 dark:text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path></svg>
                                            </div>
                                        </div>
                                        
                                        @if($record->audit_trail)
                                        <div class="mt-4">
                                            <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2">Approval History</span>
                                            <div class="space-y-2">
                                                @foreach($record->audit_trail as $step)
                                                    <div class="flex items-center text-[10px] text-gray-500 space-x-2">
                                                        <span class="font-bold uppercase">{{ $step['to'] }}</span>
                                                        <span>by</span>
                                                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ fetch_data(\App\Models\User::find($step['user_id'])?->name ?? 'System' ?? null) }}</span>
                                                        <span>at</span>
                                                        <span>{{ fetch_data(\Illuminate\Support\Carbon::parse($step['timestamp'])?->format('d M H:i') ?? null) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="bg-background-light/50 px-6 py-3 flex items-center justify-between border-t border-border-main">
                                    <div class="flex items-center space-x-4">
                                        <button class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-blue-600 transition flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Print Receipt
                                        </button>
                                        <button class="text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-blue-600 transition flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View Full Record
                                        </button>
                                    </div>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 italic">Reference: {{ fetch_data($record?->reference ?? null) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-surface rounded-3xl border border-dashed border-border-main shadow-sm">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h3 class="mt-2 text-sm font-medium text-primary dark:text-white">No withdrawal records found</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Traditional handwritten records are best kept digitally.</p>
            </div>
        @endforelse
    </div>

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
        <div class="bg-surface border shadow-xl rounded-2xl p-4 flex items-center space-x-3" 
            :class="type === 'success' ? 'border-emerald-100 dark:border-emerald-900/30' : 'border-rose-100 dark:border-rose-900/30'">
            <div :class="type === 'success' ? 'text-emerald-500' : 'text-rose-500'">
                <template x-if="type === 'success'">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </div>
            <p class="text-sm font-bold text-primary dark:text-white" x-text="message"></p>
        </div>
    </div>
</div>

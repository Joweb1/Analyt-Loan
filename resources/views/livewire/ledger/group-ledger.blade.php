<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    {{-- Fixed Back Button --}}
    <a href="{{ route('ledger.dashboard') }}" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-white/20 rounded-full text-slate-900 hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </a>

    {{-- Sticky Ledger Header --}}
    <div class="max-w-5xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('ledger.dashboard') }}" class="w-10 h-10 bg-white rounded-sm border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter uppercase italic">{{ $group }} Ledger</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Notebook Mode</span>
                        <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">{{ count($members) }} Entries Loaded</span>
                    </div>
                </div>
            </div>

            {{-- Premium Search --}}
            <div class="relative group w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 group-focus-within:text-indigo-500 transition-colors">search</span>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search entries by name or ID..." 
                    class="block w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-sm text-sm font-medium placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all shadow-sm">
            </div>
        </div>
    </div>

    {{-- Member Ledger Timeline (Stacked Cards) --}}
    <div class="max-w-5xl mx-auto space-y-4">
        @forelse($members as $member)
            <div class="bg-white rounded-sm border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
                {{-- Card Header: Member Info & Status --}}
                <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white">
                    <div class="flex items-center gap-4 flex-1 cursor-pointer" wire:click="toggleExpand('{{ $member['id'] }}')">
                        <div class="w-10 h-10 bg-slate-50 rounded-sm border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden shrink-0">
                            <span class="material-symbols-outlined text-xl">person</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-black text-slate-900 tracking-tight uppercase truncate">{{ $member['name'] }}</h3>
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-sm text-[8px] font-black uppercase tracking-widest shrink-0">
                                    {{ $member['loan_index'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ $member['custom_id'] }}</span>
                                <div class="w-1 h-1 rounded-full bg-slate-200"></div>
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    {{ $member['active_loan'] ? $member['active_loan']->repayment_cycle : 'No Active Loan' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Status Pill --}}
                        @php
                            $statusColors = [
                                'Paid' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                'Due Today' => 'text-amber-600 bg-amber-50 border-amber-100',
                                'Overdue' => 'text-rose-600 bg-rose-50 border-rose-100',
                                'Upcoming' => 'text-blue-600 bg-blue-50 border-blue-100',
                                'Missed' => 'text-slate-600 bg-slate-50 border-slate-100',
                            ];
                            $currentColor = $statusColors[$member['status']] ?? 'text-slate-600 bg-slate-50 border-slate-100';
                        @endphp
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $currentColor }}">
                            {{ $member['status'] }}
                        </span>

                        <button wire:click="toggleExpand('{{ $member['id'] }}')" class="w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400 transition-colors">
                            <span class="material-symbols-outlined transition-transform duration-300 {{ ($expanded[$member['id']] ?? false) ? 'rotate-180' : '' }}">
                                keyboard_arrow_down
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Expanded Content --}}
                @if($expanded[$member['id']] ?? false)
                    <div class="border-t border-slate-100 animate-in slide-in-from-top-2 duration-300">
                        <div class="p-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
                            {{-- Left: Financial Metadata --}}
                            <div class="lg:col-span-5 space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Due Amount</span>
                                        <span class="text-xl font-black text-rose-600">{{ $member['due_amount']->format() }}</span>
                                        <span class="block text-[9px] font-bold text-slate-400 mt-0.5">Next Due: {{ $member['next_due_date'] }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5">Outstanding</span>
                                        <span class="text-xl font-black text-slate-900">{{ $member['outstanding_balance']->format() }}</span>
                                        <span class="block text-[9px] font-bold text-slate-400 mt-0.5">Last Pay: {{ $member['last_payment_date'] }}</span>
                                    </div>
                                </div>

                                {{-- Savings Context --}}
                                <div class="p-4 bg-blue-50/50 rounded-sm border border-blue-100">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[9px] font-black text-blue-800 uppercase tracking-widest">Savings Balance</span>
                                        <span class="material-symbols-outlined text-xs text-blue-400">account_balance_wallet</span>
                                    </div>
                                    <p class="text-lg font-black text-blue-900">{{ $member['savings_account'] ? $member['savings_account']->balance->format() : 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Right: Quick Collection Entry --}}
                            <div class="lg:col-span-7 border-t lg:border-t-0 lg:border-l border-slate-100 pt-10 lg:pt-0 lg:pl-10">
                                @if($member['status'] !== 'Paid' || ($editing[$member['id']] ?? false))
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Loan Repayment</label>
                                                <button wire:click="toggleMethod('{{ $member['id'] }}', 'repayment')" class="flex items-center gap-1 group">
                                                    @if($paymentData[$member['id']]['repayment_method'] === 'cash')
                                                        <span class="material-symbols-outlined text-xs text-emerald-500">payments</span>
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Cash</span>
                                                    @else
                                                        <span class="material-symbols-outlined text-xs text-blue-500">account_balance</span>
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Transfer</span>
                                                    @endif
                                                </button>
                                            </div>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-xs font-black text-slate-300 italic">₦</span>
                                                </div>
                                                <input type="number" wire:model.blur="paymentData.{{ $member['id'] }}.repayment" 
                                                    class="block w-full pl-8 pr-4 py-3 bg-white border border-slate-200 rounded-sm text-sm font-black text-slate-900 focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all">
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Savings Deposit</label>
                                                <button wire:click="toggleMethod('{{ $member['id'] }}', 'savings')" class="flex items-center gap-1 group">
                                                    @if($paymentData[$member['id']]['savings_method'] === 'cash')
                                                        <span class="material-symbols-outlined text-xs text-emerald-500">payments</span>
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Cash</span>
                                                    @else
                                                        <span class="material-symbols-outlined text-xs text-blue-500">account_balance</span>
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest group-hover:text-indigo-600 transition-colors">Transfer</span>
                                                    @endif
                                                </button>
                                            </div>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-xs font-black text-slate-300 italic">₦</span>
                                                </div>
                                                <input type="number" wire:model.blur="paymentData.{{ $member['id'] }}.savings" 
                                                    class="block w-full pl-8 pr-4 py-3 bg-white border border-slate-200 rounded-sm text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Collection Notes</label>
                                        <input type="text" wire:model.blur="paymentData.{{ $member['id'] }}.notes" placeholder="Optional notes for this entry..." 
                                            class="block w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-sm text-xs font-medium italic focus:bg-white transition-all">
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <button wire:click="recordPayment('{{ $member['id'] }}')" 
                                            class="flex-1 px-6 py-4 bg-slate-900 text-white rounded-sm text-[11px] font-black uppercase tracking-[0.3em] hover:bg-indigo-600 shadow-lg shadow-slate-200 transition-all active:scale-[0.98]">
                                            {{ ($editing[$member['id']] ?? false) ? 'Update Entries' : 'Commit Entries' }}
                                        </button>
                                        @if($editing[$member['id']] ?? false)
                                            <button wire:click="$set('editing.{{ $member['id'] }}', false)" 
                                                class="px-6 py-4 bg-slate-100 text-slate-500 rounded-sm text-[11px] font-black uppercase tracking-[0.3em] hover:bg-slate-200 transition-all">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-center p-6 bg-emerald-50/30 rounded-sm border border-emerald-100 border-dashed">
                                        <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 mb-4 shadow-sm">
                                            <span class="material-symbols-outlined text-2xl">verified</span>
                                        </div>
                                        <h4 class="text-sm font-black text-emerald-900 uppercase tracking-tight">Ledger Entry Complete</h4>
                                        <p class="mt-1 text-xs text-emerald-600 font-medium italic">Payments for today have been verified and applied.</p>
                                        
                                        <button wire:click="editPayment('{{ $member['id'] }}')" class="mt-4 flex items-center gap-1 text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:text-indigo-800 transition-colors">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                            Edit Entry
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-8 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-xs">history</span>
                                    Audit History
                                </div>
                                <a href="{{ route('borrower.profile', ['borrower' => $member['id']]) }}" class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                                    <span class="material-symbols-outlined text-xs">edit_note</span>
                                    View Profile
                                </a>
                            </div>
                            <div class="flex items-center gap-1.5 text-[9px] font-black text-slate-300 uppercase tracking-widest">
                                Staff: {{ auth()->user()->name }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-20 text-center bg-white rounded-sm border border-slate-200 border-dashed">
                <span class="material-symbols-outlined text-5xl text-slate-200 mb-4">person_search</span>
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">No Members Found</h3>
                <p class="text-sm text-slate-400 font-medium">Try adjusting your search or selecting a different group.</p>
            </div>
        @endforelse
    </div>

    {{-- Visual Separator --}}
    <div class="max-w-5xl mx-auto flex items-center justify-center py-20">
        <span class="h-px w-20 bg-slate-200"></span>
        <span class="mx-6 text-[10px] font-black text-slate-300 uppercase tracking-[0.8em]">End of Ledger</span>
        <span class="h-px w-20 bg-slate-200"></span>
    </div>
</div>

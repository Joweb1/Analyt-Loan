<div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    {{-- Fixed Back Button --}}
    <a href="{{ route('records') }}" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-white/20 rounded-full text-slate-900 hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </a>

    {{-- Header --}}
    <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Daily Savings Ledger</h1>
                <p class="mt-2 text-sm text-slate-500 font-medium">Daily thrift and high-frequency deposit management.</p>
                <div class="mt-4 flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-100 shadow-sm">
                        {{ $thriftCycle }}-Day Cycle
                    </span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Operational Week Active</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                {{-- Date Selector --}}
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 group-focus-within:text-blue-500 transition-colors">calendar_month</span>
                    </div>
                    <input type="date" wire:model.live="selectedDate" 
                        class="block w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-sm text-sm font-black focus:outline-none focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-sm">
                </div>

                {{-- Premium Search --}}
                <div class="relative group w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 group-focus-within:text-blue-500 transition-colors">search</span>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search daily savers..." 
                        class="block w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-sm text-sm font-medium placeholder-slate-400 focus:outline-none focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all shadow-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="max-w-7xl mx-auto mb-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Collections: Today</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-2xl font-black text-slate-900 tracking-tighter italic">₦{{ fetch_data($this?->summary['today']?->format() ?? null) }}</h4>
                    <span class="material-symbols-outlined text-blue-500">payments</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Collections: This Week</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-2xl font-black text-slate-900 tracking-tighter italic">₦{{ fetch_data($this?->summary['week']?->format() ?? null) }}</h4>
                    <span class="material-symbols-outlined text-emerald-500">account_balance_wallet</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Collections: This Month</p>
                <div class="flex items-center justify-between">
                    <h4 class="text-2xl font-black text-slate-900 tracking-tighter italic">₦{{ fetch_data($this?->summary['month']?->format() ?? null) }}</h4>
                    <span class="material-symbols-outlined text-purple-500">savings</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Ledger Cards --}}
    <div class="max-w-7xl mx-auto space-y-10">
        @forelse($customers as $customer)
            <div class="bg-white rounded-sm border border-slate-200 shadow-sm overflow-hidden hover:shadow-xl transition-all duration-300">
                {{-- Card Header: Customer Identification --}}
                <div class="px-8 py-6 border-b border-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/20">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-sm border border-slate-200 flex items-center justify-center text-slate-400 overflow-hidden">
                            @if($customer['photo_url'])
                                <img src="{{ $customer['photo_url'] }}" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-2xl">person</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">{{ $customer['name'] }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $customer['id_label'] }}</span>
                                <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">
                                    Target: {{ fetch_data($customer['daily_target']?->format() ?? null) }} / Day
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Balances --}}
                    <div class="flex items-center gap-8">
                        <div class="text-right">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Week Total</span>
                            <span class="text-sm font-black text-emerald-600">{{ fetch_data($customer['week_total']?->format() ?? null) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Savings Balance</span>
                            <span class="text-sm font-black text-blue-600">{{ fetch_data($customer['daily_savings_balance']?->format() ?? null) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Daily Input Grid --}}
                <div class="p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                        @foreach($weekDays as $day)
                            <div class="relative group">
                                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center justify-between">
                                    <span>{{ $day['label'] }}</span>
                                    @if($day['is_today'])
                                        <span class="text-[8px] font-black text-blue-500 uppercase tracking-widest animate-pulse">Editable</span>
                                    @endif
                                </label>
                                
                                <div class="relative">
                                    @php
                                        $isToday = $day['date'] === $today;
                                        $isPast = $day['date'] < $today;
                                        $isFuture = $day['date'] > $today;
                                        $isAdmin = auth()->user()->isAdmin() || auth()->user()->isAppOwner();
                                        $canUnlock = $isToday || ($isAdmin && $isPast);
                                        $isUnlocked = isset($unlockedDays[$customer['user_id']][$day['date']]);
                                        $recordedAmount = $customer['week_grid'][$day['date']];
                                    @endphp

                                    @if($isUnlocked)
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-xs font-black text-slate-300 italic">₦</span>
                                        </div>
                                        <input type="number" 
                                            wire:model.blur="gridData.{{ $customer['user_id'] }}.{{ $day['date'] }}"
                                            class="block w-full pl-7 pr-12 py-3 bg-white border-2 {{ $isPast ? 'border-amber-400' : 'border-blue-500' }} rounded-sm text-sm font-black text-slate-900 focus:ring-4 focus:ring-blue-500/5 focus:border-blue-500 transition-all"
                                            placeholder="0.00">
                                        
                                        <div class="absolute right-2 top-1.5 flex items-center gap-1">
                                            @php
                                                $pm = $paymentMethods[$customer['user_id']][$day['date']] ?? 'cash';
                                            @endphp
                                            <button wire:click="togglePaymentMethod('{{ $customer['user_id'] }}', '{{ $day['date'] }}')"
                                                class="p-1.5 {{ $pm === 'cash' ? 'text-emerald-500' : 'text-blue-500' }} hover:bg-slate-50 rounded-sm transition-all"
                                                title="Toggle Cash/Transfer">
                                                <span class="material-symbols-outlined text-xs">
                                                    {{ $pm === 'cash' ? 'payments' : 'account_balance' }}
                                                </span>
                                            </button>

                                            <button wire:click="recordSavings('{{ $customer['user_id'] }}', '{{ $day['date'] }}')"
                                                wire:loading.attr="disabled"
                                                class="p-1.5 bg-slate-900 text-white rounded-sm hover:bg-blue-600 transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="material-symbols-outlined text-xs">done</span>
                                            </button>
                                        </div>
                                    @else
                                        <div class="w-full px-4 py-3 {{ fetch_data($recordedAmount?->isPositive() ? 'bg-emerald-50/50 border-emerald-100' : 'bg-slate-50/50 border-slate-100' ?? null) }} border-2 rounded-sm flex items-center justify-between">
                                            <span class="text-xs font-black {{ fetch_data($recordedAmount?->isPositive() ? 'text-emerald-700' : 'text-slate-300' ?? null) }}">
                                                {{ fetch_data($recordedAmount?->isPositive() ? '₦' . $recordedAmount?->format() : '0.00' ?? null) }}
                                            </span>
                                            
                                            @if($canUnlock)
                                                <button wire:click="toggleUnlock('{{ $customer['user_id'] }}', '{{ $day['date'] }}')" 
                                                    class="material-symbols-outlined text-xs text-slate-400 hover:text-blue-500 transition-colors cursor-pointer">
                                                    lock
                                                </button>
                                            @else
                                                @if($recordedAmount->isPositive())
                                                    <span class="material-symbols-outlined text-xs text-emerald-500">verified</span>
                                                @else
                                                    <span class="material-symbols-outlined text-xs text-slate-200">lock</span>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-8 py-3 bg-slate-50/30 border-t border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('saver.profile', ['saver' => $customer['user_id']]) }}" class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-blue-600 transition-colors">
                            <span class="material-symbols-outlined text-xs">manage_accounts</span>
                            Manage Thrift
                        </a>
                        <div class="flex items-center gap-1.5 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            <span class="material-symbols-outlined text-xs">history</span>
                            Transaction Log
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-[9px] font-black text-slate-300 uppercase tracking-widest italic">
                        Daily Savings Account Active
                    </div>
                </div>
            </div>
        @empty
            <div class="py-20 text-center bg-white rounded-sm border border-slate-200 border-dashed">
                <span class="material-symbols-outlined text-5xl text-slate-200 mb-4">person_search</span>
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tighter">No Daily Savers Found</h3>
                <p class="text-sm text-slate-400 font-medium">Register customers for daily savings to see them here.</p>
            </div>
        @endforelse
    </div>

    {{-- Footer Separator --}}
    <div class="max-w-7xl mx-auto flex items-center justify-center py-20">
        <span class="h-px w-20 bg-slate-200"></span>
        <span class="mx-6 text-[10px] font-black text-slate-300 uppercase tracking-[0.8em]">End of Record</span>
        <span class="h-px w-20 bg-slate-200"></span>
    </div>
</div>

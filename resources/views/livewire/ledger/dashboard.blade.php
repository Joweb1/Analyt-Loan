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
                <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">Collection Ledger</h1>
                <p class="mt-2 text-sm text-slate-500 font-medium">Premium digital workspace for daily repayment and savings management.</p>
                <div class="mt-4 flex items-center gap-4">
                    {{-- Week Selector --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-sm">calendar_view_week</span>
                        </div>
                        <input type="date" wire:model.live="selectedDate" 
                            class="block w-full pl-9 pr-4 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-indigo-500/5 transition-all shadow-sm">
                    </div>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Viewing: {{ $currentWeekInfo }}</span>
                </div>
            </div>
            <div class="flex items-center gap-4 bg-white p-2 rounded-sm border border-slate-200 shadow-sm">
                <div class="px-4 py-2 border-r border-slate-100 text-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">System Date</span>
                    <span class="text-sm font-bold text-slate-900">{{ \App\Models\Organization::current()->getSystemTime()->format('l, d F Y') }}</span>
                </div>
                <div class="px-4 py-2 text-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-emerald-600 uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Operational
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Operational Summary Cards --}}
    <div class="max-w-7xl mx-auto mb-12">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Repayments Week --}}
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm group hover:border-emerald-200 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Weekly Repayments</h3>
                    <span class="material-symbols-outlined text-slate-200 text-sm group-hover:text-emerald-400 transition-colors">payments</span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ $stats['total_repayments_week']->format() }}</p>
            </div>

            {{-- Weekly Due --}}
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm group hover:border-amber-200 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Weekly Due</h3>
                    <span class="material-symbols-outlined text-slate-200 text-sm group-hover:text-amber-400 transition-colors">event_repeat</span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-amber-600 tracking-tight">{{ $stats['weekly_due']->format() }}</p>
            </div>

            {{-- Monthly Due --}}
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm group hover:border-purple-200 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Monthly Due</h3>
                    <span class="material-symbols-outlined text-slate-200 text-sm group-hover:text-purple-400 transition-colors">calendar_month</span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-purple-600 tracking-tight">{{ $stats['monthly_due']->format() }}</p>
            </div>

            {{-- Overdue Amount --}}
            <div class="bg-white p-6 rounded-sm border border-slate-200 shadow-sm group hover:border-rose-200 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Overdue Acc.</h3>
                    <span class="material-symbols-outlined text-slate-200 text-sm group-hover:text-rose-400 transition-colors">warning</span>
                </div>
                <p class="text-xl sm:text-2xl font-black text-rose-600 tracking-tight">{{ $stats['overdue_amount']->format() }}</p>
            </div>
        </div>
    </div>

    {{-- Collection Group Cards --}}
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">Collection Groups</h2>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Notebook View</span>
                <span class="material-symbols-outlined text-slate-300">menu_book</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($groups as $group)
                <a href="{{ route('ledger.group', ['group' => $group['name']]) }}" 
                   class="group block bg-white rounded-sm border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden relative">
                    
                    {{-- Passed Day Indicator --}}
                    @if($group['is_passed'] && $group['unpaid_indicator'] > 0)
                        <div class="absolute top-0 right-0 bg-rose-500 text-white px-3 py-1 rounded-bl-sm z-10 animate-pulse">
                            <span class="text-[9px] font-black uppercase tracking-widest">{{ $group['unpaid_indicator'] }} UNPAID</span>
                        </div>
                    @endif

                    {{-- Card Header --}}
                    <div class="px-6 py-5 border-b border-slate-50 flex items-center justify-between {{ isset($group['is_monthly']) ? 'bg-indigo-50/30' : ($group['is_passed'] ? 'bg-slate-100/50' : 'bg-slate-50/30') }}">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight uppercase">{{ $group['name'] }}</h3>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $group['members_count'] }} Members</span>
                        </div>
                        <div class="w-12 h-12 rounded-sm border border-slate-200 bg-white flex items-center justify-center text-slate-400 group-hover:text-indigo-600 group-hover:border-indigo-100 transition-colors">
                            <span class="material-symbols-outlined text-2xl">arrow_forward</span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Week's Collection</span>
                                <p class="text-sm font-black text-slate-900 tracking-tight">₦{{ $group['collected_amount']->format() }}</p>
                            </div>
                            <div class="text-right">
                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Overdue</span>
                                <p class="text-sm font-black text-rose-600 tracking-tight">₦{{ $group['overdue_amount']->format() }}</p>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Week's Coverage</span>
                                <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">{{ $group['performance'] }}%</span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $group['performance'] > 80 ? 'bg-emerald-500' : ($group['performance'] > 50 ? 'bg-amber-500' : 'bg-rose-500') }}" 
                                     style="width: {{ $group['performance'] }}%"></div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm {{ $group['unpaid_indicator'] > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                                    {{ $group['unpaid_indicator'] > 0 ? 'pending_actions' : 'check_circle' }}
                                </span>
                                <span class="text-[10px] font-black {{ $group['unpaid_indicator'] > 0 ? 'text-rose-600' : 'text-emerald-600' }} uppercase tracking-widest">
                                    {{ $group['unpaid_indicator'] }} Yet to Pay
                                </span>
                            </div>
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic group-hover:text-indigo-400 transition-colors">
                                {{ $group['members_count'] - $group['unpaid_indicator'] }} Members Paid
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Week Summary Row --}}
        <div class="mt-12 pt-12 border-t border-slate-200">
            <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase mb-8">Week Performance Summary</h2>
            <div class="grid grid-cols-2 gap-8">
                <div class="bg-emerald-50 p-6 rounded-sm border border-emerald-100 shadow-sm group hover:shadow-lg transition-all text-center sm:text-left">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Repayment Total</span>
                        <span class="material-symbols-outlined text-emerald-400 group-hover:rotate-12 transition-transform hidden sm:block">trending_up</span>
                    </div>
                    <p class="text-2xl font-black text-emerald-900 tracking-tight">{{ $stats['total_repayments_week']->format() }}</p>
                </div>

                <div class="bg-blue-50 p-6 rounded-sm border border-blue-100 shadow-sm group hover:shadow-lg transition-all text-center sm:text-left">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest">Org Savings Balance</span>
                        <span class="material-symbols-outlined text-blue-400 group-hover:rotate-12 transition-transform hidden sm:block">account_balance_wallet</span>
                    </div>
                    <p class="text-2xl font-black text-blue-900 tracking-tight">{{ $stats['total_savings_all']->format() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

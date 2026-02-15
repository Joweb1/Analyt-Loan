<div class="p-0 space-y-6">
    <!-- Header with Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-primary dark:text-white">Collections Overview</h2>
            <p class="text-xs text-slate-500 mt-1">Track payments and recovery performance</p>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Repayments Records Button -->
            <a href="{{ route('repayments.records') }}" class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 py-2 px-4 rounded-xl text-xs font-bold shadow-sm hover:bg-slate-200 transition-colors">
                <span class="material-symbols-outlined text-sm">history</span>
                Repayments
            </a>

            <!-- Focus Mode Toggle -->
            <div x-data="{ active: @entangle('showSummary') }" class="flex items-center gap-2 cursor-pointer" @click="active = !active">
                <span class="text-xs font-bold text-slate-500 uppercase">Summary</span>
                <button type="button" 
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    :class="{ 'bg-primary': active, 'bg-slate-200 dark:bg-slate-700': !active }"
                    role="switch" 
                    :aria-checked="active">
                    <span class="sr-only">Use setting</span>
                    <span aria-hidden="true" 
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        :class="{ 'translate-x-5': active, 'translate-x-0': !active }">
                    </span>
                </button>
            </div>

            <!-- Custom Date Filter Dropdown -->
            <div x-data="{ open: false, selected: @entangle('filter') }" class="relative">
                <button @click="open = !open" @click.outside="open = false" type="button" class="flex items-center justify-between gap-2 w-40 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 py-2 pl-4 pr-3 rounded-xl text-xs font-bold shadow-sm hover:border-primary/50 transition-colors">
                    <span x-text="selected.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                    <span class="material-symbols-outlined text-sm text-slate-400" :class="{ 'rotate-180': open }">expand_more</span>
                </button>
                
                <div x-show="open" 
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 z-20 mt-2 w-40 origin-top-right rounded-xl bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                    style="display: none;">
                    <div class="py-1">
                        @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'last_week' => 'Last Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year', 'last_year' => 'Last Year'] as $val => $label)
                            <a href="#" 
                               @click.prevent="selected = '{{ $val }}'; open = false"
                               class="block px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                               :class="{ 'text-primary bg-primary/5': selected === '{{ $val }}' }">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="active" x-data="{ active: @entangle('showSummary') }" x-collapse>
        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Overdue Card -->
            <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Overdue</p>
                </div>
                <h3 class="text-2xl font-extrabold text-red-600">₦{{ number_format($stats['overdue']['value']) }}</h3>
                <div class="mt-2 flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                    <span class="material-symbols-outlined text-xs">warning</span> {{ $stats['overdue']['count'] }} Active overdue accounts
                </div>
            </div>

            <!-- Collected Card -->
            <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Collected {{ str_replace('_', ' ', ucfirst($filter)) }}</p>
                    <span class="{{ $stats['collected']['change'] >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-tighter">
                        {{ $stats['collected']['change'] >= 0 ? '+' : '' }}{{ number_format($stats['collected']['change'], 1) }}%
                    </span>
                </div>
                <h3 class="text-2xl font-extrabold text-green-600">₦{{ number_format($stats['collected']['value']) }}</h3>
                <div class="mt-2 flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                    <span class="material-symbols-outlined text-xs">check_circle</span> {{ $stats['collected']['count'] }} Transactions
                </div>
            </div>

            <!-- Recovery Rate Card -->
            <div class="bg-white dark:bg-background-dark p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Recovery Rate</p>
                    <span class="{{ $stats['recovery']['change'] >= 0 ? 'bg-blue-50 text-blue-600' : 'bg-red-50 text-red-600' }} text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-tighter">
                        {{ $stats['recovery']['change'] >= 0 ? '+' : '' }}{{ number_format($stats['recovery']['change'], 1) }}%
                    </span>
                </div>
                <h3 class="text-2xl font-extrabold text-primary dark:text-blue-400">{{ number_format($stats['recovery']['value'], 1) }}%</h3>
                <div class="mt-3 w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden">
                    <div class="bg-primary dark:bg-blue-500 h-full rounded-full" style="width: {{ min(100, $stats['recovery']['value']) }}%"></div>
                </div>
            </div>
        </div>

        <!-- Collection Tip -->
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8 mb-8">
            <div class="bg-primary text-white p-4 rounded-xl relative overflow-hidden flex flex-col justify-between">
                <div class="relative z-10 flex justify-between items-start">
                    <div>
                        <h3 class="text-base font-bold mb-1">Collection Tip</h3>
                        <p class="text-xs text-slate-300 leading-relaxed max-w-2xl">System data shows calling borrowers between 10 AM and 11 AM increases payment conversion by 24% in the Lagos region.</p>
                    </div>
                     <button class="bg-white/10 hover:bg-white/20 transition-colors border border-white/20 rounded-lg px-3 py-1.5 text-[10px] font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">lightbulb</span> Insights
                    </button>
                </div>
                <!-- Abstract decorative background -->
                <div class="absolute -right-4 -bottom-4 size-32 bg-white/5 rounded-full blur-2xl"></div>
                <div class="absolute -left-10 -top-10 size-48 bg-blue-500/10 rounded-full blur-3xl"></div>
            </div>
        </div>
    </div>

    <!-- Overdue Loans Table -->
    <div class="bg-white dark:bg-background-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold">Overdue Accounts</h3>
                <p class="text-xs text-slate-500">Requires immediate manual intervention</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                    <th class="px-6 py-3">Borrower</th>
                    <th class="px-6 py-3">Loan ID</th>
                    <th class="px-6 py-3">Days Overdue</th>
                    <th class="px-6 py-3 text-right">Amount Due</th>
                    <th class="px-6 py-3 text-center">Quick Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($overdueLoans as $loan)
                    @php
                        $daysOverdue = floor($loan->updated_at->diffInDays(now()));
                        $riskColor = $daysOverdue > 30 ? 'red' : ($daysOverdue > 7 ? 'amber' : 'yellow');
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors group cursor-pointer" onclick="window.location='{{ route('loan.show', $loan->id) }}'">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="size-8 rounded-full bg-cover bg-center" style="background-image: url('{{ $loan->borrower->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($loan->borrower->user->name) }}')"></div>
                                <div>
                                    <p class="text-xs font-bold">{{ $loan->borrower->user->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $loan->borrower->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-xs font-mono text-slate-500">{{ $loan->loan_number }}</td>
                        <td class="px-6 py-3">
                            <span class="bg-{{ $riskColor }}-100 text-{{ $riskColor }}-700 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-{{ $riskColor }}-200">{{ intval($daysOverdue) }} DAYS</span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <p class="text-xs font-extrabold">₦{{ number_format($loan->amount, 2) }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button class="size-7 flex items-center justify-center rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors" title="WhatsApp">
                                    <span class="material-symbols-outlined text-sm">chat</span>
                                </button>
                                <button class="size-7 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Call">
                                    <span class="material-symbols-outlined text-sm">call</span>
                                </button>
                                <button class="flex items-center gap-1 px-2 py-1 rounded-lg bg-primary text-white text-[10px] font-bold hover:bg-slate-800 transition-all">
                                    <span class="material-symbols-outlined text-[10px]">payments</span>
                                    LOG
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-xs">
                            No overdue accounts found. Good job!
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800">
            {{ $overdueLoans->links() }}
        </div>
    </div>
    
    <!-- Floating Action Button -->
    <button class="fixed bottom-8 right-8 size-12 bg-primary text-white rounded-full shadow-2xl flex items-center justify-center group hover:scale-110 transition-transform active:scale-95 z-20">
        <span class="material-symbols-outlined text-2xl">add_card</span>
        <div class="absolute right-14 px-3 py-1.5 bg-slate-900 text-white text-[10px] font-bold rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
            Log Collection
        </div>
    </button>
</div>
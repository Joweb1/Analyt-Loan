<div class="p-2 max-w-7xl mx-auto w-full space-y-8" x-data="{ showTerminal: false }">
    <!-- Section Title -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold tracking-tight dark:text-white">Loan Center</h2>
            <p class="text-gray-500 text-sm">Performance Status engine</p>
        </div>
        <div class="flex items-center gap-4">
            @if(Auth::user()->organization && Auth::user()->organization->kyc_status !== 'approved')
                <div class="flex items-center gap-2 px-4 py-2 bg-amber-50 text-amber-700 border border-amber-100 rounded-xl text-xs font-bold animate-pulse">
                    <span class="material-symbols-outlined text-sm">info</span>
                    KYC Pending Approval
                </div>
            @endif
            <!-- Terminal Toggle -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Terminal</span>
                <button @click="showTerminal = !showTerminal" :class="showTerminal ? 'bg-primary' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                    <span :class="showTerminal ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            </div>

            <a href="{{ route('status-board') }}" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-white/5 border border-[#dbdee6] rounded-2xl text-sm font-bold text-[#111318] dark:text-white hover:bg-background-light transition-colors">
                <span class="material-symbols-outlined text-lg">monitoring</span>
                Status Board
            </a>
        </div>
    </div>
    <div class="w-full max-w-[600px] mx-auto">
        <a href="{{ route('loan.create') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-base">add</span> New Loan
            </a>        </div>
    
    <!-- Control Cards (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Repaid Today -->
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Repaid Today</p>
                <div class="p-1 bg-green-50 rounded-lg">
                    <span class="material-symbols-outlined text-green-600 text-base">payments</span>
                </div>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-xl font-black dark:text-white tracking-tight">₦{{ number_format($repaidToday, 2) }}</h3>
                <span class="flex items-center text-green-600 text-[9px] font-bold mb-0.5 bg-green-50 px-1.5 py-0.5 rounded-full">
                    Daily
                </span>
            </div>
        </div>

        <!-- Total Lent (Month) -->
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Total Lent</p>
                <div class="p-1 bg-blue-50 rounded-lg">
                    <span class="material-symbols-outlined text-blue-600 text-base">outbox</span>
                </div>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-xl font-black dark:text-white tracking-tight">₦{{ number_format($totalLent, 2) }}</h3>
                <span class="flex items-center text-blue-600 text-[9px] font-bold mb-0.5 bg-blue-50 px-1.5 py-0.5 rounded-full">
                    This Month
                </span>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Active Customers</p>
                <div class="p-1 bg-purple-50 rounded-lg">
                    <span class="material-symbols-outlined text-purple-600 text-base">group</span>
                </div>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-xl font-black dark:text-white tracking-tight">{{ number_format($activeCustomers) }}</h3>
                <span class="flex items-center text-purple-600 text-[9px] font-bold mb-0.5 bg-purple-50 px-1.5 py-0.5 rounded-full">
                    Total
                </span>
            </div>
        </div>

        <!-- Overdue Amount -->
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Overdue Amount</p>
                <div class="p-1 bg-red-50 rounded-lg">
                    <span class="material-symbols-outlined text-red-600 text-base">warning</span>
                </div>
            </div>
            <div class="flex items-end justify-between">
                <h3 class="text-xl font-black dark:text-white tracking-tight">₦{{ number_format($overdueAmount, 2) }}</h3>
                <span class="flex items-center text-red-600 text-[9px] font-bold mb-0.5 bg-red-50 px-1.5 py-0.5 rounded-full">
                    At Risk
                </span>
            </div>
        </div>
    </div>

    <!-- Loan Pipeline & Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Loan Pipeline Funnel -->
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex-1">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-base dark:text-white">Loan Pipeline</h3>
                <!-- Filter Dropdown -->
                <div class="relative">
                    <select wire:model.live="filter" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-[10px] font-bold rounded-lg py-1.5 pl-2 pr-7 focus:outline-none focus:bg-white focus:border-gray-500 uppercase tracking-wide cursor-pointer">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>
            </div>
            <div class="space-y-3" wire:loading.class="opacity-50 transition-opacity">
                <!-- Funnel Stages -->
                <div class="flex items-center gap-3">
                    <div class="w-20 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Applied</div>
                    <div class="flex-1 h-10 bg-primary/10 rounded-lg relative overflow-hidden group hover:bg-primary/20 transition-colors">
                        <div class="absolute inset-y-0 left-0 bg-primary w-full opacity-100 flex items-center px-3 justify-between">
                            <span class="text-white text-xs font-bold">{{ $pipelineApplied }} Requests</span>
                            <span class="text-white/60 text-[10px] font-medium">In Queue</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-20 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Approved</div>
                    <div class="flex-1 flex justify-center">
                        <div class="w-full h-10 bg-primary/10 rounded-lg relative overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-green-600 w-full flex items-center px-3 justify-between">
                                <span class="text-white text-xs font-bold">{{ $pipelineApproved }} Approved</span>
                                <span class="text-white/60 text-[10px] font-medium">Processed</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-20 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Declined</div>
                    <div class="flex-1 flex justify-center">
                        <div class="w-full h-10 bg-primary/10 rounded-lg relative overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-red-500 w-full flex items-center px-3 justify-between">
                                <span class="text-white text-xs font-bold">{{ $pipelineDeclined }} Declined</span>
                                <span class="text-white/60 text-[10px] font-medium">Rejected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Pulse Chart -->
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <h3 class="font-bold text-base dark:text-white mb-4">Collection Pulse</h3>
            <div class="space-y-4">
                @php
                    $total = array_sum($collectionPulse['series']);
                    $active = $collectionPulse['series'][0] ?? 0;
                    $repaid = $collectionPulse['series'][1] ?? 0;
                    $overdue = $collectionPulse['series'][2] ?? 0;
                    
                    $activePct = $total > 0 ? ($active / $total) * 100 : 33;
                    $repaidPct = $total > 0 ? ($repaid / $total) * 100 : 33;
                    $overduePct = $total > 0 ? ($overdue / $total) * 100 : 33;

                    $y1 = 150 - ($repaidPct * 1.2);
                    $y2 = 150 - ($activePct * 1.2);
                    $y3 = 150 - ($overduePct * 1.2);
                @endphp
                
                <div class="relative h-32 w-full">
                    <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 400 150">
                        <path d="M0 150 C 50 150, 100 {{ $y1 }}, 200 {{ $y2 }} S 350 {{ $y3 }}, 400 150" fill="none" stroke="#0f1729" stroke-width="3" stroke-linecap="round"></path>
                        <circle cx="100" cy="{{ $y1 }}" r="4" fill="#10b981"></circle>
                        <circle cx="200" cy="{{ $y2 }}" r="4" fill="#3b82f6"></circle>
                        <circle cx="300" cy="{{ $y3 }}" r="4" fill="#ef4444"></circle>
                    </svg>
                </div>
                
                <div class="flex justify-between text-[10px] font-bold uppercase text-gray-500">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>Active: {{ round($activePct) }}%</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span>Repaid: {{ round($repaidPct) }}%</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>Overdue: {{ round($overduePct) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Center & Terminal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Action Box (Inbox Style) -->
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-base dark:text-white">Action Center</h3>
                <a href="{{ route('actions') }}" class="text-xs font-bold text-primary hover:underline">View All Tasks</a>
            </div>
            <div class="flex flex-col gap-3 flex-1 overflow-y-auto max-h-[300px]">
                @forelse($actionItems as $item)
                    <div class="group flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-sm transition-all cursor-pointer" onclick="window.location='{{ $item['link'] }}'">
                        <div class="mt-0.5">
                            <div class="w-5 h-5 rounded border-2 border-slate-300 group-hover:border-primary flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[14px] text-white group-hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity">check</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-primary dark:text-white leading-tight truncate">{{ $item['message'] }}</p>
                            <p class="text-[10px] text-gray-500 mt-1 uppercase font-bold tracking-wider">{{ \Carbon\Carbon::parse($item['date'])->diffForHumans() }}</p>
                        </div>
                        @if($item['type'] === 'overdue')
                            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-1.5 py-0.5 rounded">CRITICAL</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-500 text-sm italic">All caught up!</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- System Health Terminal -->
        <div x-show="showTerminal" x-transition class="bg-primary rounded-xl border border-white/10 shadow-xl flex flex-col overflow-hidden">
            <div class="bg-primary px-4 py-3 border-b border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex gap-1.5">
                        <div class="size-2.5 rounded-full bg-red-500"></div>
                        <div class="size-2.5 rounded-full bg-yellow-500"></div>
                        <div class="size-2.5 rounded-full bg-green-500"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest ml-4">System Health Terminal</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="size-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-[10px] text-green-500 font-bold uppercase tracking-widest">Active</span>
                </div>
            </div>
            <div class="p-4 flex-1 font-mono text-xs overflow-y-auto terminal-scroll space-y-2 h-64">
                <p class="text-gray-400">[14:32:01] <span class="text-green-400">SUCCESS</span> Auto-check complete for #AL-8821</p>
                <p class="text-gray-400">[14:32:05] <span class="text-blue-400">INFO</span> Triggered 42 WhatsApp payment reminders</p>
                <p class="text-gray-400">[14:32:15] <span class="text-yellow-400">WARN</span> Credit score below threshold for User #4410</p>
                <p class="text-gray-400">[14:33:42] <span class="text-green-400">SUCCESS</span> Repayment of ₦45,000 reconciled for AL-302</p>
                <p class="text-gray-400">[14:35:00] <span class="text-blue-400">INFO</span> Identity verification verified: Adeola John</p>
                <p class="text-gray-400">[14:35:05] <span class="text-green-400">SUCCESS</span> Disbursed ₦250,000 to AL-9011</p>
                <p class="text-gray-400">[14:35:20] <span class="text-blue-400">INFO</span> Batch update: Loan status moved to 'Closed' (12)</p>
                <p class="text-gray-400">[14:36:10] <span class="text-green-400">SUCCESS</span> BVN Match confirmed for new application</p>
            </div>
            <div class="p-3 bg-white/5 border-t border-white/5 flex items-center gap-2">
                <span class="text-primary-foreground/50 material-symbols-outlined text-sm">chevron_right</span>
                <input class="bg-transparent border-none p-0 text-xs font-mono text-white focus:ring-0 w-full" placeholder="Type a command..." type="text"/>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-col gap-4">
    @php
        $org = \App\Models\Organization::current();
    @endphp

    @if($org && $org->system_date && $org->system_date->toDateString() !== now()->toDateString())
        <div class="bg-orange-600 text-white px-6 py-3 rounded-sm shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500 border-l-4 border-orange-800">
            <div class="flex items-center gap-4">
                <div class="size-10 rounded-sm bg-white/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl font-black">history</span>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest">System Date Active</h4>
                    <p class="text-[11px] text-white/80 font-bold leading-tight">Operating at <b class="text-white">{{ fetch_data(now()?->format('l, F d, Y') ?? null) }}</b>. All metrics reflect this date.</p>
                </div>
            </div>
            <a href="{{ route('settings') }}" class="px-4 py-1.5 bg-white text-orange-600 rounded-sm text-[10px] font-black uppercase tracking-widest hover:bg-orange-50 transition-colors shadow-sm">
                Manage Clock
            </a>
        </div>
    @endif

    <!-- Welcome/Context -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Financial Pulse</h1>
            <div class="flex items-center gap-3">
                <p class="text-xs text-slate-500 font-medium tracking-wide">Live system overview and performance metrics.</p>
                @if($selectedPortfolioId)
                    <span class="px-2 py-0.5 bg-primary/10 rounded-sm text-[9px] font-black text-primary uppercase tracking-widest border border-primary/20">
                        Portfolio: {{ fetch_data(collect($portfolios)?->firstWhere('id', $selectedPortfolioId)?->name ?? 'Unknown' ?? null) }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-[#1a1f2e] p-1.5 rounded-sm shadow-sm border border-slate-200 dark:border-slate-800">
            <!-- View Toggle -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-sm">
                <button wire:click="$set('selectedPortfolioId', null)" 
                        class="px-3 py-1.5 rounded-sm text-[9px] font-black uppercase tracking-widest transition-all {{ !$selectedPortfolioId ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
                    Org Wide
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="px-3 py-1.5 rounded-sm text-[9px] font-black uppercase tracking-widest transition-all flex items-center gap-2 {{ $selectedPortfolioId ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
                        <span>{{ $selectedPortfolioId ? 'By Portfolio' : 'Select Portfolio' }}</span>
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-zinc-900 rounded-sm shadow-2xl border border-slate-200 dark:border-zinc-800 z-50 overflow-hidden py-1">
                        @forelse($portfolios as $p)
                            <button wire:click="$set('selectedPortfolioId', '{{ fetch_data($p?->id ?? null) }}'); open = false;" 
                                    class="w-full text-left px-4 py-2 text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-zinc-800 dark:text-white flex items-center justify-between group transition-colors">
                                <span>{{ fetch_data($p?->name ?? null) }}</span>
                                @if($selectedPortfolioId === $p->id)
                                    <span class="material-symbols-outlined text-primary text-sm">check_circle</span>
                                @endif
                            </button>
                        @empty
                            <div class="px-4 py-3 text-center">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">No portfolios found</p>
                                <a href="{{ route('settings.portfolios') }}" class="text-[9px] text-primary font-black uppercase mt-1 inline-block hover:underline">Create One</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-800 hidden sm:block"></div>

            <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                @can('approve_loans')
                    <a href="{{ route('loan.approval') }}" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 text-primary dark:text-white rounded-sm text-[9px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all border border-slate-200 dark:border-slate-700">
                        <span class="material-symbols-outlined text-base">fact_check</span> Approvals
                    </a>
                @endcan
                <a href="{{ route('loan.create') }}" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-5 py-1.5 bg-primary text-white rounded-sm text-[9px] font-black uppercase tracking-widest hover:bg-primary/90 transition-all shadow-md shadow-primary/10">
                    <span class="material-symbols-outlined text-base font-bold">add</span> New Loan
                </a>
            </div>
        </div>
    </div>

    @if($selectedPortfolioId)
        <!-- Portfolio Specific Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Portfolio Balance -->
            <div class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-primary">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Portfolio Balance</dt>
                <dd class="text-xl font-black text-primary dark:text-white">₦ {{ fetch_data($portfolioBalance?->format() ?? null) }}</dd>
                <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">Active Lending Value</p>
            </div>

            <!-- Savings Balance -->
            <div class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-emerald-500">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Savings Amount</dt>
                <dd class="text-xl font-black text-emerald-600">₦ {{ fetch_data($savingsBalance?->format() ?? null) }}</dd>
                <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">Vaulted Security</p>
            </div>

            <!-- Portfolio at Risk (PAR) -->
            <div class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-rose-500">
                <div class="flex justify-between items-start mb-1">
                    <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">PAR ({{ $parPercentage }}%)</dt>
                </div>
                <dd class="text-xl font-black text-rose-600">₦ {{ fetch_data($portfolioAtRisk?->format() ?? null) }}</dd>
                <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">Overdue Principal</p>
            </div>

            <!-- Profit and Loss (PnL) -->
            <div class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-slate-900 dark:border-l-white">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Profit & Loss (PnL)</dt>
                <dd class="text-xl font-black {{ fetch_data($profitLoss?->isPositive() ? 'text-green-600' : ($profitLoss?->isNegative() ? 'text-rose-600' : 'text-slate-900 dark:text-white') ?? null) }}">
                    ₦ {{ fetch_data($profitLoss?->format() ?? null) }}
                </dd>
                <p class="text-[8px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">Net Performance</p>
            </div>
        </div>
    @endif

    <!-- Health Cards Grid -->
    <div class="space-y-4 mb-10">
        {{-- Row 1: Global Totals --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Loaned Card -->
            <a href="{{ route('loan') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-blue-500 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Org. Total Balance</dt>
                <dd class="text-xl font-black text-blue-600 group-hover:scale-[1.02] transition-transform origin-left">₦ {{ fetch_data($totalLoaned?->format() ?? null) }}</dd>
            </a>
            
            <!-- Total Collected Card -->
            <a href="{{ route('collections') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-emerald-500 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Collected</dt>
                <dd class="text-xl font-black text-emerald-600 group-hover:scale-[1.02] transition-transform origin-left">₦ {{ fetch_data($totalCollected?->format() ?? null) }}</dd>
            </a>

            <!-- Total Active Loans -->
            <a href="{{ route('status-board') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-orange-500 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Active Loans</dt>
                <dd class="text-xl font-black text-orange-600 group-hover:scale-[1.02] transition-transform origin-left">{{ number_format($activeLoansCount) }}</dd>
            </a>

            <!-- Total Customers -->
            <a href="{{ route('customer') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-purple-500 group relative">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Total Customers</dt>
                        <dd class="text-xl font-black text-purple-600 group-hover:scale-[1.02] transition-transform origin-left">{{ number_format($totalCustomers) }}</dd>
                    </div>
                    
                    <div class="flex items-center gap-2 mt-2 pt-2 border-t border-slate-50 dark:border-slate-800/50">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase leading-none">{{ number_format($borrowersCount) }}</span>
                            <span class="text-[7px] font-bold text-slate-500 uppercase tracking-tighter">Borrowers</span>
                        </div>
                        <div class="w-[1px] h-4 bg-slate-100 dark:bg-slate-800"></div>
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase leading-none">{{ number_format($saversCount) }}</span>
                            <span class="text-[7px] font-bold text-slate-500 uppercase tracking-tighter">Savers</span>
                        </div>
                        <div class="w-[1px] h-4 bg-slate-100 dark:bg-slate-800"></div>
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase leading-none">{{ number_format($guarantorsCount) }}</span>
                            <span class="text-[7px] font-bold text-slate-500 uppercase tracking-tighter">Guarantors</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Row 2: Detailed Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Fully Paid Loans -->
            <a href="{{ route('status-board') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-green-600 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Paid Loans</dt>
                <dd class="text-xl font-black text-green-700 dark:text-green-500 group-hover:scale-[1.02] transition-transform origin-left">{{ number_format($paidLoansCount) }}</dd>
            </a>

            <!-- Defaulted Loans -->
            <a href="{{ route('status-board') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-rose-600 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Defaulted Loans</dt>
                <dd class="text-xl font-black text-rose-600 group-hover:scale-[1.02] transition-transform origin-left">{{ number_format($defaultedLoansCount) }}</dd>
            </a>

            <!-- Pending Apps -->
            <a href="{{ route('loans.pending') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-blue-400 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Pending Apps</dt>
                <dd class="text-xl font-black text-blue-500 group-hover:scale-[1.02] transition-transform origin-left">{{ number_format($pendingApplicationsCount) }}</dd>
            </a>

            <!-- Live Account Balance -->
            <a href="{{ route('cashbook.month-record') }}" class="bg-white dark:bg-[#1a1f2b] overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 rounded-sm p-4 transition hover:shadow-md border-l-4 border-l-indigo-600 group">
                <dt class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Live Bank Balance</dt>
                <dd class="text-xl font-black text-indigo-600 group-hover:scale-[1.02] transition-transform origin-left">₦ {{ fetch_data($accountBalance?->format() ?? '0.00' ?? null) }}</dd>
            </a>
        </div>
    </div>
    <!-- Main Section: Chart & Inbox -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Collections Pulse Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1a1f2b] rounded-sm p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col min-h-[420px]" x-data="{ activePoint: null }">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-blue opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-blue"></span>
                        </span>
                        Collections Pulse
                    </h3>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">7-Day Recovery Trend</p>
                </div>
                <div class="flex flex-col text-right">
                    <span class="text-2xl font-black text-primary dark:text-white leading-none">₦ {{ fetch_data(number_format(collect($pulseData)?->sum('amount')) ?? null) }}</span>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Total This Week</span>
                </div>
            </div>
            
            @php
                $maxVal = collect($pulseData)->max('amount') ?: 1000;
                $height = 200;
                $width = 800;
                $padding = 40;
                $chartHeight = $height - ($padding * 2);
                
                $points = [];
                foreach($pulseData as $index => $data) {
                    $x = ($width / 6) * $index;
                    $y = $height - $padding - (($data['amount'] / $maxVal) * $chartHeight);
                    $points[] = "$x,$y";
                }
                $pathData = "M " . implode(" L ", $points);
                
                // For the area fill
                $fillPoints = $points;
                $fillPoints[] = ($width) . "," . $height;
                $fillPoints[] = "0," . $height;
                $fillPath = "M " . implode(" L ", $fillPoints) . " Z";
            @endphp

            <div class="relative flex-1 w-full mt-4 group">
                <!-- Tooltip -->
                <div 
                    x-show="activePoint !== null" 
                    x-cloak
                    class="absolute z-30 bg-slate-900 text-white p-2 rounded-sm shadow-2xl pointer-events-none transition-all duration-200 -translate-x-1/2 -translate-y-full mb-4 border border-white/10"
                    :style="`left: ${activePoint?.x}px; top: ${activePoint?.y}px`"
                >
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[9px] font-black uppercase text-slate-400" x-text="activePoint?.day"></span>
                        <span class="text-xs font-black" x-text="'₦ ' + activePoint?.formatted"></span>
                    </div>
                </div>

                <!-- SVG Chart -->
                <svg class="w-full h-full overflow-visible" viewBox="0 0 800 200" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="pulse-area-gradient" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.1"></stop>
                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"></stop>
                        </linearGradient>
                    </defs>

                    <!-- Horizontal Grid Lines -->
                    @foreach(range(0, 4) as $i)
                        @php $gridY = $padding + ($i * ($chartHeight / 4)); @endphp
                        <line x1="0" y1="{{ $gridY }}" x2="800" y2="{{ $gridY }}" stroke="currentColor" class="text-slate-100 dark:text-slate-800/50" stroke-width="1"></line>
                    @endforeach

                    <!-- Area Fill -->
                    <path d="{{ $fillPath }}" fill="url(#pulse-area-gradient)" class="transition-all duration-700"></path>

                    <!-- Main Line -->
                    <path d="{{ $pathData }}" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-700"></path>

                    <!-- Interactive Markers -->
                    @foreach($pulseData as $index => $data)
                        @php
                            $x = ($width / 6) * $index;
                            $y = $height - $padding - (($data['amount'] / $maxVal) * $chartHeight);
                        @endphp
                        <circle 
                            cx="{{ $x }}" 
                            cy="{{ $y }}" 
                            r="5" 
                            fill="#3b82f6" 
                            stroke="white" 
                            stroke-width="2"
                            class="cursor-pointer transition-all duration-300 hover:r-6"
                            @mouseenter="activePoint = { x: ({{ $index }} * (100/6)) * ($el.closest('div').clientWidth/100), y: ({{ $y }}/200) * $el.closest('div').clientHeight, day: '{{ $data['day'] }}', formatted: '{{ $data['formatted'] }}' }"
                            @mouseleave="activePoint = null"
                        ></circle>
                    @endforeach
                </svg>

                <!-- X-Axis Labels -->
                <div class="flex justify-between mt-6 px-1">
                    @foreach($pulseData as $data)
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $data['day'] }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Portfolio Composition Cards -->
            <div class="grid grid-cols-3 gap-4 mt-10 pt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-green"></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Repaid</span>
                    </div>
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ fetch_data($repaidAmount?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-1 border-x border-slate-100 dark:border-slate-800 px-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-blue"></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Active</span>
                    </div>
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ fetch_data($activeAmount?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-1 pl-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-red"></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Overdue</span>
                    </div>
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ fetch_data($overdueAmount?->format() ?? null) }}</span>
                </div>
            </div>
        </div>
        <!-- Action Inbox -->
        <div class="lg:col-span-1 bg-white dark:bg-[#1a1f2b] rounded-sm p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col h-full">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white tracking-tight">Action Inbox</h3>
                <span class="px-2 py-0.5 bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest rounded-full">{{ count($actionItems) }} Pending</span>
            </div>
            <div class="flex flex-col gap-2 flex-1 overflow-y-auto">
                @forelse($actionItems as $item)
                    <div class="group flex items-start gap-3 p-3 rounded-sm bg-slate-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 border border-transparent hover:border-slate-100 dark:hover:border-slate-700 transition-all cursor-pointer" onclick="window.location='{{ $item['link'] }}'">
                        <div class="mt-0.5">
                            <div class="w-4 h-4 rounded-sm border-2 border-slate-300 dark:border-slate-600 group-hover:border-primary flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[12px] text-primary opacity-0 group-hover:opacity-100 transition-opacity">check</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-900 dark:text-white leading-tight">{{ $item['title'] }}</p>
                            <p class="text-[10px] text-slate-500 font-medium mt-0.5 uppercase tracking-wide">{{ $item['subtitle'] }}</p>
                        </div>
                        @if($item['priority'] === 'urgent')
                            <span class="bg-rose-50 text-rose-600 text-[8px] font-black px-1.5 py-0.5 rounded-full border border-rose-100 uppercase tracking-widest">Urgent</span>
                        @else
                            <span class="text-slate-400 text-[9px] font-bold">{{ $item['time'] }}</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest italic">All caught up!</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('actions') }}" class="w-full inline-block text-center py-2 text-[10px] font-black uppercase tracking-widest text-primary dark:text-white bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-sm transition-colors border border-slate-200 dark:border-slate-700">
                    View all tasks
                </a>
            </div>
        </div>
    </div>
</div>

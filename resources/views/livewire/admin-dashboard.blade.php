<div class="flex flex-col gap-4">
    @php
        $org = Auth::user()->organization;
    @endphp

    @if($org && $org->use_manual_date)
        <div class="bg-orange-600 text-white px-6 py-4 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-full bg-white/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl font-black">history</span>
                </div>
                <div>
                    <h4 class="text-sm font-black uppercase tracking-tight">Time Override Active</h4>
                    <p class="text-xs text-white/80 font-medium">The system is operating at <b class="text-white">{{ \App\Models\Organization::systemNow()->format('l, F d, Y') }}</b>. All metrics and timestamps reflect this date.</p>
                </div>
            </div>
            <a href="{{ route('settings') }}" class="px-5 py-2 bg-white text-orange-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-orange-50 transition-colors shadow-lg">
                Manage Clock
            </a>
        </div>
    @endif

    <!-- Welcome/Context -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-4">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black text-primary dark:text-white tracking-tighter uppercase">Financial Pulse</h2>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-black text-slate-500 uppercase tracking-widest border border-slate-200 dark:border-slate-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Live System Overview
                </span>
                @if($selectedPortfolioId)
                    <span class="px-2 py-0.5 bg-primary/10 rounded-full text-[10px] font-black text-primary uppercase tracking-widest border border-primary/20">
                        Portfolio: {{ collect($portfolios)->firstWhere('id', $selectedPortfolioId)->name ?? 'Unknown' }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-[#1a1f2e] p-2 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <!-- View Toggle -->
            <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-900 p-1 rounded-xl">
                <button wire:click="$set('selectedPortfolioId', null)" 
                        class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all {{ !$selectedPortfolioId ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
                    Org Wide
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all flex items-center gap-2 {{ $selectedPortfolioId ? 'bg-white dark:bg-slate-800 text-primary shadow-sm' : 'text-slate-500 hover:text-primary' }}">
                        <span>{{ $selectedPortfolioId ? 'By Portfolio' : 'Select Portfolio' }}</span>
                        <span class="material-symbols-outlined text-sm">expand_more</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-slate-100 dark:border-zinc-800 z-50 overflow-hidden py-1">
                        @forelse($portfolios as $p)
                            <button wire:click="$set('selectedPortfolioId', '{{ $p->id }}'); open = false;" 
                                    class="w-full text-left px-4 py-2.5 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-800 dark:text-white flex items-center justify-between group transition-colors">
                                <span>{{ $p->name }}</span>
                                @if($selectedPortfolioId === $p->id)
                                    <span class="material-symbols-outlined text-primary text-sm">check_circle</span>
                                @endif
                            </button>
                        @empty
                            <div class="px-4 py-3 text-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">No portfolios found</p>
                                <a href="{{ route('settings.portfolios') }}" class="text-[10px] text-primary font-black uppercase mt-1 inline-block hover:underline">Create One</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="h-8 w-[1px] bg-slate-100 dark:bg-slate-800 hidden sm:block"></div>

            <div class="flex gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                @can('approve_loans')
                    <a href="{{ route('loan.approval') }}" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 text-primary dark:text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-100 transition-all border border-slate-200 dark:border-slate-700">
                        <span class="material-symbols-outlined text-base">fact_check</span> Approvals
                    </a>
                @endcan
                <a href="{{ route('loan.create') }}" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 bg-primary text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-base font-bold">add</span> New Loan
                </a>
            </div>
        </div>
    </div>

    @if($selectedPortfolioId)
        <!-- Portfolio Specific Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-2 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <!-- Portfolio Balance -->
            <div class="bg-primary text-white rounded-2xl p-5 shadow-xl relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">account_balance_wallet</span>
                </div>
                <div class="relative z-10 flex flex-col gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Portfolio Balance</span>
                    <h3 class="text-2xl font-black tracking-tight">₦ {{ number_format($portfolioBalance, 2) }}</h3>
                    <div class="flex items-center gap-1 text-[9px] font-black uppercase bg-white/10 w-fit px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-[12px]">trending_up</span> Active Lending Value
                    </div>
                </div>
            </div>

            <!-- Savings Balance -->
            <div class="bg-emerald-600 text-white rounded-2xl p-5 shadow-xl relative overflow-hidden group font-mono_">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">savings</span>
                </div>
                <div class="relative z-10 flex flex-col gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Savings Amount</span>
                    <h3 class="text-2xl font-black tracking-tight">₦ {{ number_format($savingsBalance, 2) }}</h3>
                    <div class="flex items-center gap-1 text-[9px] font-black uppercase bg-white/10 w-fit px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-[12px]">lock_clock</span> Vaulted Security
                    </div>
                </div>
            </div>

            <!-- Portfolio at Risk (PAR) -->
            <div class="bg-red-600 text-white rounded-2xl p-5 shadow-xl relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">warning</span>
                </div>
                <div class="relative z-10 flex flex-col gap-3">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Portfolio At Risk (PAR)</span>
                        <span class="bg-white/20 px-2 py-0.5 rounded-lg text-[10px] font-black">{{ $parPercentage }}%</span>
                    </div>
                    <h3 class="text-2xl font-black tracking-tight">₦ {{ number_format($portfolioAtRisk, 2) }}</h3>
                    <div class="flex items-center gap-1 text-[9px] font-black uppercase bg-white/10 w-fit px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-[12px]">dangerous</span> Overdue Principal
                    </div>
                </div>
            </div>

            <!-- Profit and Loss (PnL) -->
            <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-xl relative overflow-hidden group border border-white/5">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">insights</span>
                </div>
                <div class="relative z-10 flex flex-col gap-3">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white/60">Profit & Loss (PnL)</span>
                    <h3 class="text-2xl font-black tracking-tight {{ $profitLoss >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        ₦ {{ number_format($profitLoss, 2) }}
                    </h3>
                    <div class="flex items-center gap-1 text-[9px] font-black uppercase bg-white/10 w-fit px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-[12px]">analytics</span> Net Performance
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Health Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Total Loaned Card -->
        <a href="{{ route('loan') }}" class="md:col-span-1 bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-blue/10 relative overflow-hidden group block">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-symbols-outlined text-[80px] text-brand-blue">account_balance_wallet</span>
            </div>
            <div class="flex flex-col gap-4 relative z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-brand-blue/10 flex items-center justify-center text-brand-blue">
                        <span class="material-symbols-outlined text-sm font-bold">payments</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Organisation Total balance</p>
                </div>
                <div>
                    <h3 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">₦ {{ number_format($totalLoaned, 2) }}</h3>
                </div>
            </div>
        </a>
        <!-- Total Collected Card -->
        <div class="md:col-span-1 bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft transition-all duration-300 border border-transparent relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-symbols-outlined text-[80px] text-brand-green">savings</span>
            </div>
            <div class="flex flex-col gap-4 relative z-10">
                <div class="flex items-center justify-between">
                    <a href="{{ route('collections') }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <div class="w-8 h-8 rounded-full bg-brand-green/10 flex items-center justify-center text-brand-green">
                            <span class="material-symbols-outlined text-sm font-bold">trending_up</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Collected</p>
                    </a>
                    
                    <!-- Integrated Pending Apps Badge -->
                    <a href="{{ route('loans.pending') }}" class="flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full hover:bg-blue-100 transition-colors">
                        <span class="material-symbols-outlined text-[14px] font-bold">pending_actions</span>
                        <span class="text-[10px] font-black uppercase tracking-tight">{{ number_format($pendingApplicationsCount) }} Pending Apps</span>
                    </a>
                </div>
                <div>
                    <h3 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">₦ {{ number_format($totalCollected, 2) }}</h3>
                </div>
            </div>
        </div>
        <!-- Stats Grid -->
        <div class="md:col-span-2 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Customers -->
            <a href="{{ route('customer') }}" class="bg-white dark:bg-[#1a1f2b] rounded-xl p-4 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-purple/10 relative overflow-hidden group block">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[60px] text-brand-purple">groups</span>
                </div>
                <div class="flex flex-col gap-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-purple/10 flex items-center justify-center text-brand-purple">
                            <span class="material-symbols-outlined text-xs font-bold">groups</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">Total Customers</p>
                    </div>
                    <h3 class="text-purple-800 dark:text-white text-3xl font-extrabold tracking-tight text-center">{{ number_format($totalCustomers) }}</h3>
                </div>
            </a>
            <!-- Total Active Loans -->
            <a href="{{ route('status-board') }}" class="bg-white dark:bg-[#1a1f2b] rounded-xl p-4 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-orange/10 relative overflow-hidden group block">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[60px] text-brand-orange">donut_large</span>
                </div>
                <div class="flex flex-col gap-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-orange/10 flex items-center justify-center text-brand-orange">
                            <span class="material-symbols-outlined text-xs font-bold">donut_large</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">Active Loans</p>
                    </div>
                    <h3 class="text-orange-800 dark:text-white text-3xl font-extrabold tracking-tight text-center">{{ number_format($activeLoansCount) }}</h3>
                </div>
            </a>
            <!-- Fully Paid Loans -->
            <a href="{{ route('status-board') }}" class="bg-white dark:bg-[#1a1f2b] rounded-xl p-4 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-green/10 relative overflow-hidden group block">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[60px] text-brand-green">check_circle</span>
                </div>
                <div class="flex flex-col gap-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-green/10 flex items-center justify-center text-brand-green">
                            <span class="material-symbols-outlined text-xs font-bold">check_circle</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">Paid Loans</p>
                    </div>
                    <h3 class="text-emerald-800 dark:text-white text-3xl font-extrabold tracking-tight text-center">{{ number_format($paidLoansCount) }}</h3>
                </div>
            </a>
            <!-- Risk/Default Loans -->
            <a href="{{ route('status-board') }}" class="bg-white dark:bg-[#1a1f2b] rounded-xl p-4 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-red/10 relative overflow-hidden group block">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <span class="material-symbols-outlined text-[60px] text-brand-red">cancel</span>
                </div>
                <div class="flex flex-col gap-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-brand-red/10 flex items-center justify-center text-brand-red">
                            <span class="material-symbols-outlined text-xs font-bold">cancel</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">Defaulted Loans</p>
                    </div>
                    <h3 class="text-red-800 dark:text-white text-3xl font-extrabold tracking-tight text-center">{{ number_format($defaultedLoansCount) }}</h3>
                </div>
            </a>
        </div>
    </div>
    <!-- Main Section: Chart & Inbox -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Collections Pulse Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft flex flex-col min-h-[420px]" x-data="{ activePoint: null }">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-primary dark:text-white text-lg font-black tracking-tight flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-blue opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-blue"></span>
                        </span>
                        Collections Pulse
                    </h3>
                    <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">7-Day Recovery Trend</p>
                </div>
                <div class="flex flex-col text-right">
                    <span class="text-2xl font-black text-primary dark:text-white leading-none">₦ {{ number_format(collect($pulseData)->sum('amount')) }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total This Week</span>
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
                    class="absolute z-30 bg-primary text-white p-3 rounded-xl shadow-2xl pointer-events-none transition-all duration-200 -translate-x-1/2 -translate-y-full mb-4 border border-white/10"
                    :style="`left: ${activePoint?.x}px; top: ${activePoint?.y}px`"
                >
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[10px] font-black uppercase text-slate-400" x-text="activePoint?.day"></span>
                        <span class="text-sm font-black" x-text="'₦ ' + activePoint?.formatted"></span>
                    </div>
                    <div class="absolute bottom-[-6px] left-1/2 -translate-x-1/2 w-3 h-3 bg-primary rotate-45 border-r border-b border-white/10"></div>
                </div>

                <!-- SVG Chart -->
                <svg class="w-full h-full overflow-visible" viewBox="0 0 800 200" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="pulse-area-gradient" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.2"></stop>
                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"></stop>
                        </linearGradient>
                        <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                            <feGaussianBlur stdDeviation="4" result="blur"></feGaussianBlur>
                            <feComposite in="SourceGraphic" in2="blur" operator="over"></feComposite>
                        </filter>
                    </defs>

                    <!-- Horizontal Grid Lines -->
                    @foreach(range(0, 4) as $i)
                        @php $gridY = $padding + ($i * ($chartHeight / 4)); @endphp
                        <line x1="0" y1="{{ $gridY }}" x2="800" y2="{{ $gridY }}" stroke="currentColor" class="text-slate-100 dark:text-slate-800/50" stroke-width="1" stroke-dasharray="4 4"></line>
                    @endforeach

                    <!-- Area Fill -->
                    <path d="{{ $fillPath }}" fill="url(#pulse-area-gradient)" class="transition-all duration-700"></path>

                    <!-- Main Line -->
                    <path d="{{ $pathData }}" fill="none" stroke="#3b82f6" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-700" filter="url(#glow)"></path>

                    <!-- Interactive Markers -->
                    @foreach($pulseData as $index => $data)
                        @php
                            $x = ($width / 6) * $index;
                            $y = $height - $padding - (($data['amount'] / $maxVal) * $chartHeight);
                        @endphp
                        <circle 
                            cx="{{ $x }}" 
                            cy="{{ $y }}" 
                            r="6" 
                            fill="#3b82f6" 
                            stroke="white" 
                            stroke-width="3"
                            class="cursor-pointer transition-all duration-300 hover:r-8 hover:fill-primary dark:hover:fill-white"
                            @mouseenter="activePoint = { x: ({{ $index }} * (100/6)) * ($el.closest('div').clientWidth/100), y: ({{ $y }}/200) * $el.closest('div').clientHeight, day: '{{ $data['day'] }}', formatted: '{{ $data['formatted'] }}' }"
                            @mouseleave="activePoint = null"
                        ></circle>
                    @endforeach
                </svg>

                <!-- X-Axis Labels -->
                <div class="flex justify-between mt-6 px-1">
                    @foreach($pulseData as $data)
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $data['day'] }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Portfolio Composition Cards -->
            <div class="grid grid-cols-3 gap-4 mt-10 pt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-green"></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Repaid</span>
                    </div>
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ number_format($repaidAmount) }}</span>
                </div>
                <div class="flex flex-col gap-1 border-x border-slate-100 dark:border-slate-800 px-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-blue"></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Active</span>
                    </div>
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ number_format($activeAmount) }}</span>
                </div>
                <div class="flex flex-col gap-1 pl-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-red"></span>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Overdue</span>
                    </div>
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ number_format($overdueAmount) }}</span>
                </div>
            </div>
        </div>
        <!-- Action Inbox -->
        <div class="lg:col-span-1 bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-primary dark:text-white text-lg font-bold">Action Inbox</h3>
                <span class="bg-primary/10 text-primary text-xs font-bold px-2 py-1 rounded-md">{{ count($actionItems) }} Pending</span>
            </div>
            <div class="flex flex-col gap-3 flex-1 overflow-y-auto">
                @forelse($actionItems as $item)
                    <div class="group flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-sm transition-all cursor-pointer" onclick="window.location='{{ $item['link'] }}'">
                        <div class="mt-0.5">
                            <div class="w-5 h-5 rounded border-2 border-slate-300 group-hover:border-primary flex items-center justify-center transition-colors">
                                <span class="material-symbols-outlined text-[14px] text-white group-hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity">check</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-primary dark:text-white leading-tight">{{ $item['title'] }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $item['subtitle'] }}</p>
                        </div>
                        @if($item['priority'] === 'urgent')
                            <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-1.5 py-0.5 rounded">URGENT</span>
                        @else
                            <span class="text-slate-400 text-[10px]">{{ $item['time'] }}</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-500 text-sm italic">All caught up!</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('actions') }}" class="w-full inline-block text-center py-2 text-xs font-bold text-primary dark:text-white bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    View all tasks
                </a>
            </div>
        </div>
    </div>
</div>

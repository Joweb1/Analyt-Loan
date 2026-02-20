<div class="p-2 max-w-7xl mx-auto w-full space-y-8" 
    x-data="{ showTerminal: false }"
    x-init="
        showTerminal = localStorage.getItem('terminal_visible') === 'true';
        $watch('showTerminal', value => localStorage.setItem('terminal_visible', value))
    "
>
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

        <!-- Collections Pulse Chart -->
        <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft flex flex-col min-h-[420px]" x-data="{ activePoint: null }">
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
                    <span class="text-sm font-black text-primary dark:text-white">₦ {{ number_format($overdueAmountTotal) }}</span>
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
        <div x-show="showTerminal" x-cloak x-transition>
            <livewire:components.system-terminal />
        </div>
    </div>
</div>

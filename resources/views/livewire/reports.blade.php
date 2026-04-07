<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Financial Reports</h2>
            <p class="text-slate-500 font-medium">Global and periodic analytics for your lending performance.</p>
        </div>

        <!-- Top Lifetime Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <!-- Org Balance (Lifetime Snapshot) -->
            <div class="bg-slate-900 dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-slate-800 shadow-2xl group hover:border-blue-500/50 transition-colors">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/20 flex items-center justify-center text-blue-400">
                        <span class="material-symbols-outlined text-3xl">account_balance</span>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-tight">Organisation Balance</p>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Current Outstanding (Principal + Interest)</p>
                    </div>
                </div>
                <h3 class="text-4xl font-black text-white">₦ {{ number_format($orgBalance ?? 0, 2) }}</h3>
            </div>

            <!-- Expected Interest (Lifetime) -->
            <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-zinc-800 shadow-sm relative group hover:border-blue-500/50 transition-colors">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500">
                            <span class="material-symbols-outlined text-3xl">trending_up</span>
                        </div>
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-tight">Total Expected Interest</p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Lifetime Projections</p>
                        </div>
                    </div>
                    <div class="text-left md:text-right bg-blue-50 dark:bg-blue-900/20 px-4 py-2 rounded-2xl border border-blue-100 dark:border-blue-800/50">
                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Remaining to Collect</p>
                        <p class="text-xl font-black text-blue-600">₦ {{ number_format($remainingInterest ?? 0, 2) }}</p>
                    </div>
                </div>
                <h3 class="text-4xl font-black text-slate-900 dark:text-white">₦ {{ number_format($totalInterest, 2) }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Already Collected:</span>
                    <span class="text-xs font-black text-green-600">₦ {{ number_format($totalPaidInterest, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex flex-wrap items-center gap-3 bg-slate-100 dark:bg-zinc-800/50 p-1.5 rounded-2xl border border-slate-200 dark:border-zinc-700">
                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $type => $label)
                    <button wire:click="setReportType('{{ $type }}')" 
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all {{ $reportType === $type ? 'bg-white dark:bg-zinc-700 shadow-md text-primary dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
                
                <div x-data="{ open: false, start: @entangle('customStartDate'), end: @entangle('customEndDate') }" class="relative">
                    <button @click="open = !open" 
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-all {{ $reportType === 'custom' ? 'bg-white dark:bg-zinc-700 shadow-md text-primary dark:text-white' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        Custom Range
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak 
                        class="absolute left-0 md:right-0 md:left-auto mt-3 w-72 bg-white dark:bg-zinc-900 p-6 rounded-3xl shadow-2xl border border-slate-100 dark:border-zinc-800 z-50">
                        <h4 class="text-xs font-black uppercase tracking-widest mb-4">Select Range</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Start Date</label>
                                <input type="date" x-model="start" class="w-full bg-slate-50 dark:bg-zinc-800 border-none rounded-xl text-sm font-bold">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">End Date</label>
                                <input type="date" x-model="end" class="w-full bg-slate-50 dark:bg-zinc-800 border-none rounded-xl text-sm font-bold">
                            </div>
                            <button @click="$wire.setCustomDates(start, end); open = false" 
                                class="w-full py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20">
                                Apply Range
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if($reportType === 'custom' && $customStartDate && $customEndDate)
                <div class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800/50 rounded-xl inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 text-sm">calendar_today</span>
                    <span class="text-[10px] font-black uppercase text-blue-700 dark:text-blue-400 tracking-widest">
                        {{ \Carbon\Carbon::parse($customStartDate)->format('M d') }} - {{ \Carbon\Carbon::parse($customEndDate)->format('M d, Y') }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Filtered Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Disbursed -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-primary/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Disbursed</p>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white">₦ {{ number_format($disbursed, 2) }}</h3>
            </div>

            <!-- Collected -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-green-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-green-500/10 flex items-center justify-center text-green-500">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Collected</p>
                </div>
                <h3 class="text-2xl font-black text-green-600">₦ {{ number_format($collected, 2) }}</h3>
            </div>

            <!-- Net Savings -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-emerald-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                        <span class="material-symbols-outlined">savings</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Net Savings</p>
                </div>
                <h3 class="text-2xl font-black {{ ($totalSavings ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    ₦ {{ number_format($totalSavings ?? 0, 2) }}
                </h3>
            </div>

            <!-- Total Loans -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-orange-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-500">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Loans</p>
                </div>
                <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($totalLoansCount ?? 0) }}</h3>
            </div>

            <!-- New Customers -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-purple-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-purple-500/10 flex items-center justify-center text-purple-500">
                        <span class="material-symbols-outlined">group_add</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">New Customers</p>
                </div>
                <h3 class="text-2xl font-black text-purple-600">{{ number_format($newCustomers) }}</h3>
            </div>

            <!-- Risk (PAR) -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-red-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500">
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Portfolio Risk (PAR)</p>
                </div>
                <h3 class="text-2xl font-black text-red-600">₦ {{ number_format($totalPAR, 2) }}</h3>
            </div>

            <!-- Profit (PnL) -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-3xl border border-slate-100 dark:border-zinc-800 shadow-sm group hover:border-indigo-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500">
                        <span class="material-symbols-outlined">analytics</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Gross Profit (PnL)</p>
                </div>
                <h3 class="text-2xl font-black {{ $totalPnL >= 0 ? 'text-green-600' : 'text-red-600' }}">₦ {{ number_format($totalPnL, 2) }}</h3>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Disbursed vs Collected -->
            <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-zinc-800 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    Cash Flow Analysis
                </h3>
                <div class="h-[350px]">
                    <canvas id="cashFlowChart"></canvas>
                </div>
            </div>

            <!-- Interest Analysis -->
            <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-zinc-800 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Interest Performance
                </h3>
                <div class="h-[350px]">
                    <canvas id="interestChart"></canvas>
                </div>
            </div>

            <!-- Savings & Customers -->
            <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-zinc-800 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Vault & Customer Growth
                </h3>
                <div class="h-[350px]">
                    <canvas id="savingsCustomersChart"></canvas>
                </div>
            </div>

            <!-- Loan Volume -->
            <div class="bg-white dark:bg-zinc-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-zinc-800 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-8 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    Loan Volume Count
                </h3>
                <div class="h-[350px]">
                    <canvas id="loanVolumeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Lower Section: Print & Export -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Printable Reports Section -->
            <div class="bg-white dark:bg-zinc-900 rounded-[2.5rem] p-10 border border-slate-100 dark:border-zinc-800 shadow-sm group">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-2xl bg-green-500/10 flex items-center justify-center text-green-600">
                        <span class="material-symbols-outlined">print</span>
                    </div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Organisation Reports</h3>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    @foreach(['daily' => 'Daily Report', 'weekly' => 'Weekly Report', 'monthly' => 'Monthly Report', 'yearly' => 'Annual Report'] as $type => $label)
                        <a href="{{ route('report.print', ['type' => $type]) }}" target="_blank" 
                            class="px-5 py-3 bg-slate-50 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Data Export Section -->
            <div class="bg-slate-900 dark:bg-zinc-900 rounded-[2.5rem] p-10 border border-slate-800 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-8 opacity-10">
                    <span class="material-symbols-outlined text-[100px] text-white">download</span>
                </div>
                
                <div class="relative z-10">
                    <h3 class="text-sm font-black text-white uppercase tracking-widest mb-8 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        Export Data (CSV)
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="exportLoans" class="flex items-center gap-3 p-4 bg-slate-800/50 hover:bg-primary rounded-2xl transition-all group border border-slate-700">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-white">payments</span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-300 group-hover:text-white">Loans</span>
                        </button>
                        <button wire:click="exportCustomers" class="flex items-center gap-3 p-4 bg-slate-800/50 hover:bg-primary rounded-2xl transition-all group border border-slate-700">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-white">group</span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-300 group-hover:text-white">Customers</span>
                        </button>
                        <button wire:click="exportCollateral" class="flex items-center gap-3 p-4 bg-slate-800/50 hover:bg-primary rounded-2xl transition-all group border border-slate-700">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-white">inventory_2</span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-300 group-hover:text-white">Collateral</span>
                        </button>
                        <button wire:click="exportStaff" class="flex items-center gap-3 p-4 bg-slate-800/50 hover:bg-primary rounded-2xl transition-all group border border-slate-700">
                            <span class="material-symbols-outlined text-slate-400 group-hover:text-white">badge</span>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-300 group-hover:text-white">Staffs</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let charts = {};

            const createCharts = (data) => {
                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold', size: 10 } } } },
                    scales: {
                        y: { beginAtZero: true, grid: { display: false }, ticks: { font: { weight: 'bold' } } },
                        x: { grid: { display: false }, ticks: { font: { weight: 'bold' } } }
                    }
                };

                // Cash Flow Chart
                if (charts.cashFlow) charts.cashFlow.destroy();
                charts.cashFlow = new Chart(document.getElementById('cashFlowChart'), {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Disbursed', data: data.disbursed, borderColor: '#6366f1', backgroundColor: '#6366f120', fill: true, tension: 0.4 },
                            { label: 'Collected', data: data.collected, borderColor: '#22c55e', backgroundColor: '#22c55e20', fill: true, tension: 0.4 }
                        ]
                    },
                    options: commonOptions
                });

                // Interest Chart
                if (charts.interest) charts.interest.destroy();
                charts.interest = new Chart(document.getElementById('interestChart'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Expected', data: data.interestExpected, backgroundColor: '#3b82f6', borderRadius: 8 },
                            { label: 'Paid', data: data.interestPaid, backgroundColor: '#22c55e', borderRadius: 8 }
                        ]
                    },
                    options: commonOptions
                });

                // Savings & Customers Chart
                if (charts.savings) charts.savings.destroy();
                charts.savings = new Chart(document.getElementById('savingsCustomersChart'), {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Net Savings', data: data.savings, borderColor: '#10b981', tension: 0.4, yAxisID: 'y' },
                            { label: 'New Customers', data: data.customers, borderColor: '#a855f7', borderDash: [5, 5], tension: 0.4, yAxisID: 'y1' }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: { position: 'left', grid: { display: false } },
                            y1: { position: 'right', grid: { display: false } }
                        }
                    }
                });

                // Loan Volume Chart
                if (charts.loans) charts.loans.destroy();
                charts.loans = new Chart(document.getElementById('loanVolumeChart'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{ label: 'Loans Disbursed', data: data.loans, backgroundColor: '#8b5cf6', borderRadius: 20 }]
                    },
                    options: commonOptions
                });
            };

            createCharts(@json($chartData));

            Livewire.on('chartUpdated', (event) => {
                let data = event.chartData;
                if (!data && event[0]) data = event[0].chartData;
                if (data) createCharts(data);
            });
        });
    </script>
    @endpush
</div>

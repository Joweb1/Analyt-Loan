<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h2 class="text-3xl font-black text-primary dark:text-white">Organization Reports</h2>
                <p class="text-gray-500">Analyze your lending performance and metrics.</p>
            </div>
            <div class="flex bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl">
                <button wire:click="setReportType('daily')" class="px-4 py-2 text-xs font-bold rounded-lg {{ $reportType === 'daily' ? 'bg-white dark:bg-zinc-700 shadow-sm text-primary dark:text-white' : 'text-gray-500' }}">Daily</button>
                <button wire:click="setReportType('weekly')" class="px-4 py-2 text-xs font-bold rounded-lg {{ $reportType === 'weekly' ? 'bg-white dark:bg-zinc-700 shadow-sm text-primary dark:text-white' : 'text-gray-500' }}">Weekly</button>
                <button wire:click="setReportType('monthly')" class="px-4 py-2 text-xs font-bold rounded-lg {{ $reportType === 'monthly' ? 'bg-white dark:bg-zinc-700 shadow-sm text-primary dark:text-white' : 'text-gray-500' }}">Monthly</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2">Total Disbursed</p>
                <h3 class="text-3xl font-black text-primary dark:text-white">₦ {{ number_format($disbursed) }}</h3>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2">Total Collected</p>
                <h3 class="text-3xl font-black text-green-600">₦ {{ number_format($collected) }}</h3>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2">New Customers</p>
                <h3 class="text-3xl font-black text-purple-600">{{ number_format($newCustomers) }}</h3>
            </div>
        </div>

        <!-- Action Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Data Export Section -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 border border-slate-100 dark:border-zinc-800 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">download</span>
                    Data Exports (CSV)
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <button wire:click="exportLoans" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl hover:bg-primary hover:text-white transition-all group">
                        <span class="material-symbols-outlined text-slate-400 group-hover:text-white">payments</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 group-hover:text-white">Loan Data</span>
                    </button>
                    <button wire:click="exportCustomers" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl hover:bg-primary hover:text-white transition-all group">
                        <span class="material-symbols-outlined text-slate-400 group-hover:text-white">group</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 group-hover:text-white">Customer Data</span>
                    </button>
                    <button wire:click="exportCollateral" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl hover:bg-primary hover:text-white transition-all group">
                        <span class="material-symbols-outlined text-slate-400 group-hover:text-white">inventory_2</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 group-hover:text-white">Collateral Data</span>
                    </button>
                    <button wire:click="exportStaff" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl hover:bg-primary hover:text-white transition-all group">
                        <span class="material-symbols-outlined text-slate-400 group-hover:text-white">badge</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 group-hover:text-white">Staffs Data</span>
                    </button>
                </div>
            </div>

            <!-- Printable Reports Section -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl p-8 border border-slate-100 dark:border-zinc-800 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-600">print</span>
                    Organisation Reports
                </h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('report.print', ['type' => 'daily']) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">Daily Report</a>
                    <a href="{{ route('report.print', ['type' => 'weekly']) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">Weekly Report</a>
                    <a href="{{ route('report.print', ['type' => 'monthly']) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">Monthly Report</a>
                    <a href="{{ route('report.print', ['type' => 'yearly']) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">Yearly Report</a>
                    <a href="{{ route('report.print', ['type' => 'staff']) }}" target="_blank" class="px-4 py-2.5 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-600 hover:text-white transition-all">Staff Performance</a>
                </div>
                <p class="mt-6 text-[10px] text-slate-400 font-medium leading-relaxed italic">
                    * Professional reports include detailed metrics on disbursals, collections, and overall portfolio health.
                </p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-primary dark:text-white">Financial Overview</h3>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-primary"></span>
                        <span class="text-gray-500">Disbursed</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-green-500"></span>
                        <span class="text-gray-500">Collected</span>
                    </div>
                </div>
            </div>
            
            <div id="chart" class="w-full h-[400px]"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            function initChart(data = null) {
                const chartElement = document.querySelector("#chart");
                if (!chartElement) return;
                
                // Use provided data or fallback to initial data from PHP
                const labels = data ? data.labels : @json($chartData['labels']);
                const disbursed = data ? data.disbursed : @json($chartData['disbursed']);
                const collected = data ? data.collected : @json($chartData['collected']);

                // Clear existing chart if any
                chartElement.innerHTML = '';

                const options = {
                    series: [{
                        name: 'Disbursed',
                        data: disbursed
                    }, {
                        name: 'Collected',
                        data: collected
                    }],
                    chart: {
                        type: 'area',
                        height: 400,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'Manrope, sans-serif',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                        }
                    },
                    colors: ['#2563eb', '#10b981'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [20, 100, 100, 100]
                        }
                    },
                    xaxis: {
                        categories: labels,
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            style: { colors: '#64748b', fontSize: '12px' }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#64748b', fontSize: '12px' },
                            formatter: function (val) {
                                return "₦" + val.toLocaleString();
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f5f9',
                        strokeDashArray: 4,
                        xaxis: { lines: { show: true } }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return "₦" + val.toLocaleString();
                            }
                        }
                    }
                };

                const chart = new ApexCharts(chartElement, options);
                chart.render();
            }

            document.addEventListener('livewire:navigated', () => initChart());
            
            document.addEventListener('livewire:init', () => {
                Livewire.on('chartUpdated', (event) => {
                    initChart(event.chartData);
                });
            });

            window.addEventListener('load', () => initChart());
        </script>
    @endpush
</div>

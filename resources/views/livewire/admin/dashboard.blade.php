<div class="flex flex-col gap-8">
    <!-- Header -->
    <div class="flex flex-col gap-1">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Platform Overview</h2>
        <p class="text-slate-500 dark:text-slate-400">Comprehensive health and growth metrics across all organizations.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-[#1a1f2b] p-6 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined">hub</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Organizations</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalOrganizations) }}</h3>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-xs font-semibold text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full">
                    {{ $activeOrganizationsCount }} Active
                </span>
                @if($pendingKycCount > 0)
                <span class="text-xs font-semibold text-amber-500 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded-full">
                    {{ $pendingKycCount }} Pending KYC
                </span>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1f2b] p-6 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Lent</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">₦{{ number_format($totalLent, 0) }}</h3>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-400 italic">Cumulative across all tenants</p>
        </div>

        <div class="bg-white dark:bg-[#1a1f2b] p-6 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined">payments</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Collected</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">₦{{ number_format($totalCollected, 0) }}</h3>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1">
                <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500" style="width: {{ $totalLent > 0 ? ($totalCollected / $totalLent) * 100 : 0 }}%"></div>
                </div>
                <span class="text-[10px] font-bold text-slate-400">{{ $totalLent > 0 ? round(($totalCollected / $totalLent) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1a1f2b] p-6 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/20 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400">
                    <span class="material-symbols-outlined">groups</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wider">Total Staff</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($totalUsers) }}</h3>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-400 italic">Total registered platform users</p>
        </div>
    </div>

    <!-- Charts & Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Activity Chart (Mocked with simple bars for now) -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1a1f2b] p-8 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Platform Repayments Trend</h3>
                <span class="text-xs font-medium text-slate-400">Last 7 Days</span>
            </div>
            <div class="flex items-end justify-between gap-2 h-48">
                @foreach($platformActivity as $data)
                    <div class="flex-1 flex flex-col items-center gap-3">
                        <div class="relative w-full group">
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                ₦{{ number_format($data['amount'], 0) }}
                            </div>
                            <div class="w-full bg-primary/10 dark:bg-primary/5 rounded-t-lg group-hover:bg-primary/20 transition-colors" 
                                 style="height: {{ $data['amount'] > 0 ? max(($data['amount'] / collect($platformActivity)->max('amount')) * 100, 5) : 2 }}%">
                                <div class="w-full h-full bg-primary rounded-t-lg opacity-80" style="height: 100%"></div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $data['day'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Recent Organizations -->
        <div class="bg-white dark:bg-[#1a1f2b] p-8 rounded-2xl shadow-soft border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">New Onboardings</h3>
                <a href="{{ route('admin.organizations') }}" class="text-xs font-bold text-primary hover:underline">View All</a>
            </div>
            <div class="flex flex-col gap-6">
                @foreach($recentOrganizations as $org)
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-50 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0">
                            @if($org->logo_path)
                                <img src="{{ $org->logo_url }}" class="w-full h-full object-contain rounded-lg">
                            @else
                                <span class="material-symbols-outlined text-slate-400">business</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $org->name }}</h4>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wide">{{ $org->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-bold {{ $org->kyc_status === 'approved' ? 'text-emerald-500' : 'text-amber-500' }}">
                                {{ strtoupper($org->kyc_status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- System Health & Platform Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white dark:bg-[#1a1f2b] p-8 rounded-3xl shadow-soft border border-slate-100 dark:border-slate-800 h-fit">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-8">Platform Health & Critical Alerts</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-4 p-4 {{ $dbStatus === 'online' ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20' : 'bg-rose-50 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/20' }} rounded-2xl border">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $dbStatus === 'online' ? 'bg-emerald-400' : 'bg-rose-400' }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $dbStatus === 'online' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                    </span>
                    <div>
                        <h4 class="text-sm font-bold {{ $dbStatus === 'online' ? 'text-emerald-800 dark:text-emerald-400' : 'text-rose-800 dark:text-rose-400' }}">
                            Database {{ ucfirst($dbStatus) }}
                        </h4>
                        <p class="text-[10px] {{ $dbStatus === 'online' ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500' }} uppercase font-medium">Latency: {{ $latency }}ms</p>
                    </div>
                </div>

                @if($pendingKycCount > 0)
                <div class="flex items-center gap-4 p-4 bg-amber-50 dark:bg-amber-500/10 rounded-2xl border border-amber-100 dark:border-amber-500/20">
                    <span class="material-symbols-outlined text-amber-500">verified_user</span>
                    <div>
                        <h4 class="text-sm font-bold text-amber-800 dark:text-amber-400">{{ $pendingKycCount }} Pending Verifications</h4>
                        <p class="text-[10px] text-amber-600 dark:text-amber-500 uppercase font-medium">Action Required</p>
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-4 p-4 bg-blue-50 dark:bg-blue-500/10 rounded-2xl border border-blue-100 dark:border-blue-500/20">
                    <span class="material-symbols-outlined text-blue-500">storage</span>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800 dark:text-blue-400">Disk Storage</h4>
                        <p class="text-[10px] text-blue-600 dark:text-blue-500 uppercase font-medium">Usage: {{ round(disk_free_space("/") / 1024 / 1024 / 1024, 1) }}GB Free</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 p-4 bg-purple-50 dark:bg-purple-500/10 rounded-2xl border border-purple-100 dark:border-purple-500/20">
                    <span class="material-symbols-outlined text-purple-500">history</span>
                    <div>
                        <h4 class="text-sm font-bold text-purple-800 dark:text-purple-400">Last Backup</h4>
                        <p class="text-[10px] text-purple-600 dark:text-purple-500 uppercase font-medium">Status: Completed</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="h-full">
            <livewire:components.admin-terminal />
        </div>
    </div>
</div>

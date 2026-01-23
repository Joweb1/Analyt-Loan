<x-app-layout>
    <div class="bg-blue-500 flex flex-col gap-4">
    <!-- Welcome/Context -->
    <div class="flex justify-between items-end">
        <div>
            <h2 class="text-2xl font-bold text-primary dark:text-white tracking-tight">Financial Pulse</h2>
            <p class="text-slate-500 text-sm mt-1">Real-time overview of your lending portfolio.</p>
        </div>
        <div class="flex gap-2">
            <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-100 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-base">download</span> Export
            </button>
            <button class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg text-xs font-medium hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-base">add</span> New Loan
            </button>
        </div>
    </div>
    <!-- Health Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Principal Card -->
        <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-blue/10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-symbols-outlined text-[80px] text-brand-blue">account_balance_wallet</span>
            </div>
            <div class="flex flex-col gap-4 relative z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-brand-blue/10 flex items-center justify-center text-brand-blue">
                        <span class="material-symbols-outlined text-sm font-bold">payments</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Principal Outstanding</p>
                </div>
                <div>
                    <h3 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">₦ 45,230,000</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="bg-brand-blue/10 text-brand-blue text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[10px]">arrow_upward</span> 12%
                        </span>
                        <span class="text-slate-400 text-xs">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Interest Card -->
        <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-green/10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-symbols-outlined text-[80px] text-brand-green">savings</span>
            </div>
            <div class="flex flex-col gap-4 relative z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-brand-green/10 flex items-center justify-center text-brand-green">
                        <span class="material-symbols-outlined text-sm font-bold">trending_up</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Interest Generated</p>
                </div>
                <div>
                    <h3 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">₦ 8,450,500</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="bg-brand-green/10 text-brand-green text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[10px]">arrow_upward</span> 5%
                        </span>
                        <span class="text-slate-400 text-xs">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Risk Card -->
        <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft hover:shadow-lg transition-all duration-300 border border-transparent hover:border-brand-red/10 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                <span class="material-symbols-outlined text-[80px] text-brand-red">warning</span>
            </div>
            <div class="flex flex-col gap-4 relative z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-brand-red/10 flex items-center justify-center text-brand-red">
                        <span class="material-symbols-outlined text-sm font-bold">error</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Risk / Default (PAR 30)</p>
                </div>
                <div>
                    <h3 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">₦ 1,200,000</h3>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="bg-brand-red/10 text-brand-red text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1">
                            <span class="material-symbols-outlined text-[10px]">arrow_downward</span> 2%
                        </span>
                        <span class="text-slate-400 text-xs">Improved vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Section: Chart & Inbox -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Collections Pulse Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-primary dark:text-white text-lg font-bold">Collections Pulse</h3>
                    <p class="text-slate-500 text-xs">Volume over last 30 days</p>
                </div>
                <div class="flex gap-2">
                    <span class="w-3 h-3 rounded-full bg-primary"></span>
                    <span class="text-xs text-slate-500">Actual</span>
                    <span class="w-3 h-3 rounded-full bg-slate-200 ml-2"></span>
                    <span class="text-xs text-slate-500">Projected</span>
                </div>
            </div>
            <div class="relative flex-1 min-h-[250px] w-full">
                <!-- SVG Chart -->
                <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 800 200">
                    <defs>
                        <linearGradient id="gradient-fill" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="#0f1729" stop-opacity="0.1"></stop>
                            <stop offset="100%" stop-color="#0f1729" stop-opacity="0"></stop>
                        </linearGradient>
                    </defs>
                    <!-- Grid Lines -->
                    <line stroke="#f1f5f9" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="800" y1="50" y2="50"></line>
                    <line stroke="#f1f5f9" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="800" y1="100" y2="100"></line>
                    <line stroke="#f1f5f9" stroke-dasharray="4 4" stroke-width="1" x1="0" x2="800" y1="150" y2="150"></line>
                    <!-- Chart Area -->
                    <path d="M0 150 C 100 140, 150 160, 200 120 S 300 40, 400 60 S 500 100, 600 80 S 700 20, 800 40 V 200 H 0 Z" fill="url(#gradient-fill)"></path>
                    <!-- Chart Line -->
                    <path d="M0 150 C 100 140, 150 160, 200 120 S 300 40, 400 60 S 500 100, 600 80 S 700 20, 800 40" fill="none" stroke="#0f1729" stroke-linecap="round" stroke-width="3"></path>
                </svg>
                <!-- X Axis Labels -->
                <div class="flex justify-between text-xs text-slate-400 mt-2 px-2">
                    <span>Nov 01</span>
                    <span>Nov 07</span>
                    <span>Nov 14</span>
                    <span>Nov 21</span>
                    <span>Nov 30</span>
                </div>
            </div>
        </div>
        <!-- Action Inbox -->
        <div class="lg:col-span-1 bg-white dark:bg-[#1a1f2b] rounded-2xl p-6 shadow-soft flex flex-col h-full">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-primary dark:text-white text-lg font-bold">Action Inbox</h3>
                <span class="bg-primary/10 text-primary text-xs font-bold px-2 py-1 rounded-md">3 Pending</span>
            </div>
            <div class="flex flex-col gap-3 flex-1 overflow-y-auto">
                <!-- Task Item 1 -->
                <div class="group flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-sm transition-all cursor-pointer">
                    <div class="mt-0.5">
                        <div class="w-5 h-5 rounded border-2 border-slate-300 group-hover:border-primary flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[14px] text-white group-hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity">check</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-primary dark:text-white leading-tight">Approve Disbursement</p>
                        <p class="text-xs text-slate-500 mt-1">#LN-2023-88 • ₦ 500,000</p>
                    </div>
                    <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-1.5 py-0.5 rounded">URGENT</span>
                </div>
                <!-- Task Item 2 -->
                <div class="group flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-sm transition-all cursor-pointer">
                    <div class="mt-0.5">
                        <div class="w-5 h-5 rounded border-2 border-slate-300 group-hover:border-primary flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[14px] text-white group-hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity">check</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-primary dark:text-white leading-tight">KYC Review Required</p>
                        <p class="text-xs text-slate-500 mt-1">Adewale T. • ID Mismatch</p>
                    </div>
                    <span class="text-slate-400 text-[10px]">2h ago</span>
                </div>
                <!-- Task Item 3 -->
                <div class="group flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-sm transition-all cursor-pointer">
                    <div class="mt-0.5">
                        <div class="w-5 h-5 rounded border-2 border-slate-300 group-hover:border-primary flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[14px] text-white group-hover:text-primary opacity-0 group-hover:opacity-100 transition-opacity">check</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-primary dark:text-white leading-tight">Resolve Failed Auto-Debits</p>
                        <p class="text-xs text-slate-500 mt-1">System Alert • 3 Accounts</p>
                    </div>
                    <div class="w-2 h-2 rounded-full bg-brand-red mt-1"></div>
                </div>
                <!-- Task Item 4 (Done) -->
                <div class="group flex items-start gap-3 p-3 rounded-xl opacity-60 hover:opacity-100 transition-opacity cursor-pointer">
                    <div class="mt-0.5">
                        <div class="w-5 h-5 rounded border-2 border-slate-300 bg-slate-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">check</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-500 line-through leading-tight">Weekly Report Generated</p>
                        <p class="text-xs text-slate-400 mt-1">Automated Task</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button class="w-full py-2 text-xs font-bold text-primary dark:text-white bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    View all tasks
                </button>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>

<x-app-layout>
    <div class="p-2 max-w-7xl mx-auto w-full space-y-8">
        <!-- Section Title -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold tracking-tight dark:text-white">Loan Center</h2>
                <p class="text-gray-500 text-sm">Performance Status engine</p>
            </div>
            <a href="{{ route('status-board') }}" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-white/5 border border-[#dbdee6] rounded-2xl text-sm font-bold text-[#111318] dark:text-white hover:bg-background-light transition-colors">
                <span class="material-symbols-outlined text-lg">monitoring</span>
                Status Board
            </a>
        </div>
        <div class="w-full max-w-[600px] mx-auto">
            <a href="{{ route('loan.create') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-base">add</span> New Loan
                </a>        </div>
        <!-- Control Cards (KPIs) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-2">Repaid Today</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-2xl font-bold dark:text-white">₦14,250,000</h3>
                    <span class="flex items-center text-green-600 text-xs font-bold mb-1">
                        <span class="material-symbols-outlined text-sm">trending_up</span> 12.5%
                    </span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-2">Pending Approvals</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-2xl font-bold dark:text-white">24</h3>
                    <span class="flex items-center text-primary dark:text-blue-400 text-xs font-bold mb-1">
                        +4 new
                    </span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-2">Overdue Amount</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-2xl font-bold dark:text-white">₦1,200,400</h3>
                    <span class="flex items-center text-red-500 text-xs font-bold mb-1">
                        <span class="material-symbols-outlined text-sm">trending_down</span> 2.1%
                    </span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition-shadow">
                <p class="text-sm font-medium text-gray-500 mb-2">Avg. Repayment Rate (%)</p>
                <div class="flex items-end justify-between">
                    <h3 class="text-2xl font-bold dark:text-white">98.2%</h3>
                    <span class="flex items-center text-green-600 text-xs font-bold mb-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span> stable
                    </span>
                </div>
            </div>
        </div>
        <!-- Middle Section: Split View -->
        <div class="flex flex-wrap lg:flex-row gap-6">
            <!-- Loan Pipeline Funnel -->
            <div class="lg:col-span-3 bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex-1">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-bold text-lg dark:text-white">Loan Pipeline</h3>
                    <span class="text-xs font-medium text-gray-400 px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded">Live View</span>
                </div>
                <div class="space-y-4">
                    <!-- Funnel Stages -->
                    <div class="flex items-center gap-4">
                        <div class="w-24 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Applied</div>
                        <div class="flex-1 h-12 bg-primary/10 rounded-lg relative overflow-hidden">
                            <div class="absolute inset-y-0 left-0 bg-primary w-full opacity-100 flex items-center px-4 justify-between">
                                <span class="text-white text-sm font-bold">142 Requests</span>
                                <span class="text-white/60 text-xs font-medium">100%</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-24 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Underwriting</div>
                        <div class="flex-1 flex justify-center">
                            <div class="w-[85%] h-12 bg-primary/10 rounded-lg relative overflow-hidden">
                                <div class="absolute inset-y-0 left-0 bg-primary/90 w-full flex items-center px-4 justify-between">
                                    <span class="text-white text-sm font-bold">84 Processing</span>
                                    <span class="text-white/60 text-xs font-medium">59%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-24 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Approved</div>
                        <div class="flex-1 flex justify-center">
                            <div class="w-[60%] h-12 bg-primary/10 rounded-lg relative overflow-hidden">
                                <div class="absolute inset-y-0 left-0 bg-primary/80 w-full flex items-center px-4 justify-between">
                                    <span class="text-white text-sm font-bold">52 Ready</span>
                                    <span class="text-white/60 text-xs font-medium">36%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-24 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Disbursed</div>
                        <div class="flex-1 flex justify-center">
                            <div class="w-[45%] h-12 bg-primary/10 rounded-lg relative overflow-hidden">
                                <div class="absolute inset-y-0 left-0 bg-primary/70 w-full flex items-center px-4 justify-between">
                                    <span class="text-white text-sm font-bold">42 Funded</span>
                                    <span class="text-white/60 text-xs font-medium">29%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- System Health Terminal -->
            <div class="lg:col-span-2 bg-primary rounded-xl border border-white/10 shadow-xl flex flex-col overflow-hidden">
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
                <div class="p-4 flex-1 font-mono text-xs overflow-y-auto terminal-scroll space-y-2">
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
        <!-- Urgent Queue Table -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg dark:text-white">Urgent Queue</h3>
                    <p class="text-sm text-gray-500">Requires immediate manual intervention</p>
                </div>
                <button class="text-sm font-semibold text-primary dark:text-blue-400 flex items-center gap-1 hover:underline">
                    View All <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Borrower</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Loan ID</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Risk Level</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Days Overdue</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">AO</div>
                                    <span class="text-sm font-semibold dark:text-white">Adebayo Omobola</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">AL-99821</td>
                            <td class="px-6 py-4 text-sm font-semibold dark:text-white">₦450,000</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-bold uppercase">Critical</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">12 Days</td>
                            <td class="px-6 py-4">
                                <button class="bg-primary text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors">Review</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-xs">CE</div>
                                    <span class="text-sm font-semibold dark:text-white">Chidi Eze</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">AL-99845</td>
                            <td class="px-6 py-4 text-sm font-semibold dark:text-white">₦120,000</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-[10px] font-bold uppercase">Medium</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">4 Days</td>
                            <td class="px-6 py-4">
                                <button class="bg-primary text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors">Review</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-xs">IK</div>
                                    <span class="text-sm font-semibold dark:text-white">Ibrahim K.</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">AL-99901</td>
                            <td class="px-6 py-4 text-sm font-semibold dark:text-white">₦800,000</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-bold uppercase">High Risk</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">Pending Identity</td>
                            <td class="px-6 py-4">
                                <button class="bg-primary text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors">Review</button>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">FM</div>
                                    <span class="text-sm font-semibold dark:text-white">Fatima Musa</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">AL-99922</td>
                            <td class="px-6 py-4 text-sm font-semibold dark:text-white">₦35,000</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-[10px] font-bold uppercase">Flagged</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">1 Day</td>
                            <td class="px-6 py-4">
                                <button class="bg-primary text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors">Review</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

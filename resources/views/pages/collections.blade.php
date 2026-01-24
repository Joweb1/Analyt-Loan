<x-app-layout>
    @section('title', 'Loan Collections Dashboard')
    <div class="p-0 space-y-8">
        <!-- Summary Stats -->
        <div>
            <h2 class="text-2xl font-extrabold text-primary dark:text-white mb-6">Collections Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-background-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Overdue</p>
                        <span class="bg-red-50 text-red-600 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-tighter">+5.2% vs LW</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-red-600">₦12,400,000</h3>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="material-symbols-outlined text-sm">warning</span> 412 Active overdue accounts
                    </div>
                </div>
                <div class="bg-white dark:bg-background-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Collected Today</p>
                        <span class="bg-green-50 text-green-600 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-tighter">On Target</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-green-600">₦1,200,000</h3>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-400 font-medium">
                        <span class="material-symbols-outlined text-sm">check_circle</span> 18 Transactions processed
                    </div>
                </div>
                <div class="bg-white dark:bg-background-dark p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start mb-4">
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Recovery Rate</p>
                        <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-tighter">+2.1% Peak</span>
                    </div>
                    <h3 class="text-3xl font-extrabold text-primary dark:text-blue-400">88%</h3>
                    <div class="mt-4 w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary dark:bg-blue-500 h-full w-[88%] rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Middle Section: Chart & Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">

            <div class="bg-primary text-white p-6 rounded-xl relative overflow-hidden flex flex-col justify-between">
                <div class="relative z-10">
                    <h3 class="text-lg font-bold mb-2">Collection Tip</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">System data shows calling borrowers between 10 AM and 11 AM increases payment conversion by 24% in the Lagos region.</p>
                </div>
                <div class="relative z-10 pt-6">
                    <button class="w-full bg-white/10 hover:bg-white/20 transition-colors border border-white/20 rounded-lg py-3 text-sm font-bold flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">lightbulb</span>
                        View More Insights
                    </button>
                </div>
                <!-- Abstract decorative background -->
                <div class="absolute -right-4 -bottom-4 size-32 bg-white/5 rounded-full blur-2xl"></div>
                <div class="absolute -left-10 -top-10 size-48 bg-blue-500/10 rounded-full blur-3xl"></div>
            </div>
        </div>
        <!-- Overdue Loans Table -->
        <div class="bg-white dark:bg-background-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Overdue Accounts</h3>
                    <p class="text-sm text-slate-500">Requires immediate manual intervention</p>
                </div>
                <button class="text-sm font-semibold text-primary dark:text-blue-400 flex items-center gap-1 hover:underline">
                    View All <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                        <th class="px-6 py-4">Borrower</th>
                        <th class="px-6 py-4">Loan ID</th>
                        <th class="px-6 py-4">Days Overdue</th>
                        <th class="px-6 py-4 text-right">Amount Due</th>
                        <th class="px-6 py-4 text-center">Quick Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <!-- Row 1 -->
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Borrower avatar of Adebayo Tunde" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA2u23U0aRrf4BrLdP_exviqumL3JHwU6LZueFC5nw1gNVO9ORrKg66_c2a2i5TeGr0Ohue9ak3KUpnCZGVWZaOhDxyQnRmNEdVdmVj0YYQucc-VSadLFvOc7SrEBFd6oktWKshEiQLgbolSBzVBkhOq0_5GqtcYDdN9UEsOyotbiAlsdxHYKvM0P71EKWLP0taFNM3a1_1JcyFiF3GJ6IPRcHNT9oj3PBOzuD-WsNNrMnhlTQ1M2A7OuIuiv9QsLEss7TUT9JedUya')"></div>
                                <div>
                                    <p class="text-sm font-bold">Adebayo Tunde</p>
                                    <p class="text-xs text-slate-500">Lagos, Nigeria</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-500">LN-88219</td>
                        <td class="px-6 py-4">
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-extrabold px-2 py-1 rounded-full border border-amber-200">14 DAYS</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-sm font-extrabold">₦450,000</p>
                            <p class="text-[10px] text-slate-400">Total: ₦1.2M</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button class="size-8 flex items-center justify-center rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors" title="WhatsApp">
                                    <span class="material-symbols-outlined text-base">chat</span>
                                </button>
                                <button class="size-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Call">
                                    <span class="material-symbols-outlined text-base">call</span>
                                </button>
                                <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary text-white text-[10px] font-bold hover:bg-slate-800 transition-all">
                                    <span class="material-symbols-outlined text-xs">payments</span>
                                    LOG PAYMENT
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 2 -->
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Borrower avatar of Chinelo Okoro" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBWLfbLeYPyeWF840JD_1DIygZ8NoCgPtB40DhvacYxGz4UToiOIEqoihWvljREYr3SyplgqwRCFwCVJqW9xb2KAVU03ricB5QUQZBJktQM55Lu11rFFsnNzV9lLIpcxzffN2p8if9brvaWh8mjyBteNwRr36gKK0vnKlEs3gnIgUDU6ruHYh2MHbU8fDoieKIOs7YR89hkCYLERVQZpwsH5rY1koXok_DoJJLlr4OT2pN75Wa4fpNmsm-XTr4W4VhHEhDmMVtkeBi1')"></div>
                                <div>
                                    <p class="text-sm font-bold">Chinelo Okoro</p>
                                    <p class="text-xs text-slate-500">Enugu, Nigeria</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-500">LN-90124</td>
                        <td class="px-6 py-4">
                            <span class="bg-red-100 text-red-700 text-[10px] font-extrabold px-2 py-1 rounded-full border border-red-200">42 DAYS</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-sm font-extrabold">₦1,250,000</p>
                            <p class="text-[10px] text-slate-400">Total: ₦3.0M</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button class="size-8 flex items-center justify-center rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                                    <span class="material-symbols-outlined text-base">chat</span>
                                </button>
                                <button class="size-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <span class="material-symbols-outlined text-base">call</span>
                                </button>
                                <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary text-white text-[10px] font-bold hover:bg-slate-800 transition-all">
                                    <span class="material-symbols-outlined text-xs">payments</span>
                                    LOG PAYMENT
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 3 -->
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Borrower avatar of Musa Ibrahim" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDiasOrUX0rOzUWoZ4s_JxFRkkMd6uZkqkTX8y-pAL_03IpkO-Ga5Gq0i4cD9Myw9AFEENbvcSswm9HbjLKYnTB2uu-IqfgTJo_a01ETttd1m1bajlLNo_ycNkqzKa1pVBuqQopObV-vtPPY71_awA0UWpu2pd7l0pWJ0t7xiqGQAN-aWkZQ4ikTtAkLxQLmKHDVqYKwtO05JCihpwiuLtbYTjRxJ64ew_HOeh7o-ElJ32SWmzL0NbgMOJxzZDAmVTEw3szJiVsJ2Pv')"></div>
                                <div>
                                    <p class="text-sm font-bold">Musa Ibrahim</p>
                                    <p class="text-xs text-slate-500">Kano, Nigeria</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-slate-500">LN-77610</td>
                        <td class="px-6 py-4">
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-extrabold px-2 py-1 rounded-full border border-amber-200">5 DAYS</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="text-sm font-extrabold">₦85,000</p>
                            <p class="text-[10px] text-slate-400">Total: ₦150k</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button class="size-8 flex items-center justify-center rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                                    <span class="material-symbols-outlined text-base">chat</span>
                                </button>
                                <button class="size-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                    <span class="material-symbols-outlined text-base">call</span>
                                </button>
                                <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary text-white text-[10px] font-bold hover:bg-slate-800 transition-all">
                                    <span class="material-symbols-outlined text-xs">payments</span>
                                    LOG PAYMENT
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <p class="text-xs text-slate-500 font-medium">Showing 3 of 412 entries</p>
                <div class="flex gap-1">
                    <button class="size-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-400 pointer-events-none">
                        <span class="material-symbols-outlined text-lg">chevron_left</span>
                    </button>
                    <button class="size-8 flex items-center justify-center rounded border border-slate-200 bg-primary text-white font-bold text-xs">1</button>
                    <button class="size-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-600 font-bold text-xs hover:bg-slate-50">2</button>
                    <button class="size-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-600 font-bold text-xs hover:bg-slate-50">3</button>
                    <button class="size-8 flex items-center justify-center rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-50">
                        <span class="material-symbols-outlined text-lg">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Floating Action Button -->
    <button class="fixed bottom-8 right-8 size-14 bg-primary text-white rounded-full shadow-2xl flex items-center justify-center group hover:scale-110 transition-transform active:scale-95 z-20">
        <span class="material-symbols-outlined text-3xl">add_card</span>
        <div class="absolute right-16 px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
            Log Collection
        </div>
    </button>
</x-app-layout>
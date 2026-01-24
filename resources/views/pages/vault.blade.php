<x-app-layout>
    @section('title', 'Collateral Vault Inventory')
    <div class="px-0 pt-4">
        <a href="{{ route('collateral.create') }}" class="flex items-center gap-3 px-6 py-3 bg-primary text-white rounded-full shadow-md hover:scale-105 active:scale-95 transition-all">
                <span class="material-symbols-outlined">add</span>
                <span class="font-bold tracking-tight">Add Collateral</span>
            </a>    </div>
    <section class="px-0 py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-[16px] border border-[#dbdee6] dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-sm font-semibold text-[#606e8a]">Total Vault Value</p>
                <div class="p-2 bg-primary/10 text-primary rounded-lg">
                    <span class="material-symbols-outlined">monetization_on</span>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-extrabold">₦743,200,000</h3>
                <span class="text-xs font-bold text-green-500 flex items-center">
                    <span class="material-symbols-outlined text-sm">trending_up</span> 2.4%
                </span>
            </div>
            <p class="text-[10px] text-[#606e8a] mt-2 font-medium tracking-wide uppercase">AI-Appraised Real-time</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-6 rounded-[16px] border border-[#dbdee6] dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-sm font-semibold text-[#606e8a]">Items Pending Release</p>
                <div class="p-2 bg-amber-500/10 text-amber-500 rounded-lg">
                    <span class="material-symbols-outlined">assignment_return</span>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold">12</h3>
            <p class="text-[10px] text-[#606e8a] mt-2 font-medium tracking-wide uppercase">Requires Verification</p>
        </div>
        <div class="bg-white dark:bg-gray-900 p-6 rounded-[16px] border border-[#dbdee6] dark:border-gray-800 shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <p class="text-sm font-semibold text-[#606e8a]">Assets in Transit</p>
                <div class="p-2 bg-blue-500/10 text-blue-500 rounded-lg">
                    <span class="material-symbols-outlined">local_shipping</span>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold">5</h3>
            <p class="text-[10px] text-[#606e8a] mt-2 font-medium tracking-wide uppercase">Inbound from Borrower</p>
        </div>
    </section>
    <section class="px-0 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white dark:bg-gray-900 rounded-[16px] border border-[#dbdee6] dark:border-gray-800">
            <div class="flex gap-2">
                <button class="flex items-center gap-2 px-4 py-1.5 bg-primary text-white text-sm font-bold rounded-lg transition-all">
                    All Assets
                </button>
                <button class="flex items-center gap-2 px-4 py-1.5 bg-[#f0f1f5] dark:bg-gray-800 text-[#111318] dark:text-white text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all">
                    In Vault
                    <span class="px-1.5 py-0.5 bg-white dark:bg-gray-700 text-[10px] rounded-md">24</span>
                </button>
                <button class="flex items-center gap-2 px-4 py-1.5 bg-[#f0f1f5] dark:bg-gray-800 text-[#111318] dark:text-white text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all">
                    In Transit
                </button>
                <button class="flex items-center gap-2 px-4 py-1.5 bg-[#f0f1f5] dark:bg-gray-800 text-[#111318] dark:text-white text-sm font-semibold rounded-lg hover:bg-gray-200 transition-all">
                    Released
                </button>
            </div>
            <div class="flex gap-2">
                <button class="flex items-center gap-2 px-3 py-1.5 text-sm font-semibold text-[#606e8a] hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-lg">filter_list</span>
                    Filters
                </button>
                <button class="flex items-center gap-2 px-3 py-1.5 text-sm font-semibold text-[#606e8a] hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-lg">download</span>
                    Export
                </button>
            </div>
        </div>
    </section>
    <section class="px-0 pb-4">
        <div class="bg-white dark:bg-gray-900 rounded-[16px] border border-[#dbdee6] dark:border-gray-800 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                <thead class="bg-[#f8f9fa] dark:bg-gray-800/50 border-b border-[#dbdee6] dark:border-gray-800">
                <tr>
                    <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Asset Item</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Borrower</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider text-right">Estimated Value</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider">Verification</th>
                    <th class="px-6 py-4 text-[11px] font-bold text-[#606e8a] uppercase tracking-wider"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[#dbdee6] dark:divide-gray-800">
                <tr class="hover:bg-[#f8f9fa] dark:hover:bg-gray-800/30 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-2xl">laptop_mac</span>
                            </div>
                            <div>
                                <p class="font-bold text-[#111318] dark:text-white leading-tight">MacBook Pro 16" M3 Max</p>
                                <p class="text-xs text-[#606e8a]">ID: VAULT-MBP-8921</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="size-6 rounded-full bg-cover bg-center" data-alt="John Doe profile" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuCW6uj8Iy6B-95p1-9hfZGEdbzAyy6jfcuYMMLQBh4FV-wg07AwOBMfJUDIilY5DyP8sr0hG_Rvg9KZOVWWlCIrHvgbW3SuuIc4lR-ICflKQv8lr32AkPtpEpRNeNWTMpjvnBOJkLHGi7WjppOoo4_LzVA")'></div>
                            <span class="text-sm font-semibold">John Doe</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold">₦4,500,000.00</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">In Vault</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-primary">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">verified</span>
                            <span class="text-xs font-bold">AI Verified</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-[#606e8a] hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-[#f8f9fa] dark:hover:bg-gray-800/30 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-2xl">watch</span>
                            </div>
                            <div>
                                <p class="font-bold text-[#111318] dark:text-white leading-tight">Rolex Submariner Date</p>
                                <p class="text-xs text-[#606e8a]">ID: VAULT-RLX-3210</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="size-6 rounded-full bg-cover bg-center" data-alt="Jane Smith profile" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuBmtzUCaUufgEjaHKn8mxTqXCTBI4Y_iJY79sWAhDFTDxElI7wDaA6Aj9FnRH8mIPujCCZMZBJHijAqEgLaHmfx7xJwWa01v-S7Bvz6iBHnPtr47yjt7Pb8APWNwwk7qDXJo0OJ3dXhk-_9TrJMeuav34RZbOymrlbxJ4XqCjlnJLArjA")'></div>
                            <span class="text-sm font-semibold">Jane Smith</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold">₦18,200,000.00</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">In Transit</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-primary">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">verified</span>
                            <span class="text-xs font-bold">Appraised</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-[#606e8a] hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-[#f8f9fa] dark:hover:bg-gray-800/30 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-2xl">cottage</span>
                            </div>
                            <div>
                                <p class="font-bold text-[#111318] dark:text-white leading-tight">Real Estate Title (Lekki)</p>
                                <p class="text-xs text-[#606e8a]">ID: VAULT-RE-1102</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="size-6 rounded-full bg-primary/10 flex items-center justify-center text-primary text-[10px] font-bold">AC</div>
                            <span class="text-sm font-semibold">Acme Corp</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold">₦720,000,000.00</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">In Vault</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-primary">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">verified</span>
                            <span class="text-xs font-bold">Auto-Verified</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-[#606e8a] hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-[#f8f9fa] dark:hover:bg-gray-800/30 transition-colors group">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                                <span class="material-symbols-outlined text-2xl">smartphone</span>
                            </div>
                            <div>
                                <p class="font-bold text-[#111318] dark:text-white leading-tight">iPhone 14 Pro Max</p>
                                <p class="text-xs text-[#606e8a]">ID: VAULT-IPH-0012</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="size-6 rounded-full bg-cover bg-center" data-alt="Michael Chen profile" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAS9QRAkV28YknSs899p9d3Tmy3WLU2JC2VkIzBKlHyKp3tx5JmXoZtA-oWu95g0SO5qtN1i1AisSAGSPGpc46GUIEWc7UW7QsFGalZzgNAt6rorxycOceIgIRQ7DCAvBdcc2l8GBEzVdwoVvpyIUfHkiPpW-Ztvr82eJLiB6YGj7AoN8EPwfNCe608eaGk7LNztk65OlGuIBMayIjARAmlLjihyWo8mSq0nr4kQBf75VwQnTxH2E6PZYCWUg67hM79uRf2j-bmsfY")'></div>
                            <span class="text-sm font-semibold">Michael Chen</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold">₦1,500,000.00</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">Released</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-1.5 text-[#606e8a]">
                            <span class="material-symbols-outlined text-base">verified</span>
                            <span class="text-xs font-bold">Archived</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button class="text-[#606e8a] hover:text-primary transition-colors">
                            <span class="material-symbols-outlined">more_vert</span>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>
            <div class="p-4 border-t border-[#dbdee6] dark:border-gray-800 flex items-center justify-between">
                <p class="text-xs text-[#606e8a] font-medium">Showing <span class="text-[#111318] dark:text-white font-bold">4</span> of <span class="text-[#111318] dark:text-white font-bold">38</span> collateral assets</p>
                <div class="flex gap-2">
                    <button class="px-3 py-1 text-xs font-bold border border-[#dbdee6] dark:border-gray-800 rounded-lg hover:bg-gray-50 transition-colors">Previous</button>
                    <button class="px-3 py-1 text-xs font-bold border border-[#dbdee6] dark:border-gray-800 rounded-lg hover:bg-gray-50 transition-colors">Next</button>
                </div>
            </div>
        </div>
    </section>

</x-app-layout>

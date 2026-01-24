<x-app-layout>
    @section('title', 'Team Management Settings')
    <div x-data="{ showInviteMemberModal: false }" class="p-0 max-w-7xl mx-auto w-full">
        <!-- Page Heading -->
        <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
            <div class="flex flex-col gap-1">
                <h2 class="text-3xl font-black tracking-tight text-primary dark:text-white">Team Management</h2>
                <p class="text-[#716b80] text-base font-medium">Manage your organization's administrative members and access levels.</p>
            </div>
            <button @click="showInviteMemberModal = true" class="flex items-center gap-2 bg-primary dark:bg-zinc-100 dark:text-primary text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform active:scale-95">
                <span class="material-symbols-outlined text-[20px]">person_add</span>
                <span>Invite Member</span>
            </button>
        </div>
        <!-- Tabs -->
        <div class="flex border-b border-[#dfdee3] dark:border-zinc-800 gap-8 mb-6 overflow-x-auto">
            <button class="border-b-2 border-primary text-primary dark:text-white dark:border-white pb-4 font-bold text-sm whitespace-nowrap px-1">All Members (12)</button>
            <button class="border-b-2 border-transparent text-[#716b80] hover:text-primary pb-4 font-bold text-sm whitespace-nowrap px-1">Active</button>
            <button class="border-b-2 border-transparent text-[#716b80] hover:text-primary pb-4 font-bold text-sm whitespace-nowrap px-1">Invited/Pending</button>
            <button class="border-b-2 border-transparent text-[#716b80] hover:text-primary pb-4 font-bold text-sm whitespace-nowrap px-1">Inactive</button>
        </div>
        <!-- Data Table Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-[#dfdee3] dark:border-zinc-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                    <tr class="bg-primary/5 dark:bg-zinc-800/50">
                        <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Last Login</th>
                        <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Assigned Loans</th>
                        <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-[#dfdee3] dark:divide-zinc-800">
                    <!-- Row 1 -->
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Professional Nigerian woman profile" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuApBpi0Q654JKPNpjNB6CBU78KtSqOCqf4vQIh9PKgL9PmA6BQVtoRzz3QNrW1CaUng-q4eA_1cJi3yMXdTPPHgxtqcBA2d0hPcoSFZMLklQjxf-HMVTTzhRQODx0gOqAVTqG8HTzo-DoS3p3ahpyAWg6C-V-BbR7SVW86Lwa3WdYe6_k6rXBmd1iZ2Q2R-VWGteHcBWnAYweEzG494_LNbFJjJvwRjoSyu-9pwsrEYf-SlsQpBEdun2QDhitVAROv3l976XH27OnpA');"></div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-bold dark:text-white">Chinelo Okafor</p>
                                    <p class="text-xs text-[#716b80]">c.okafor@analyt.ng</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full text-xs font-bold">Loan Officer</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-1.5">
                                <div class="size-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-medium dark:text-zinc-300">Active</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right text-sm text-[#716b80] dark:text-zinc-400">2 hours ago</td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-sm font-bold dark:text-white">42 Loans</p>
                            <p class="text-[10px] text-[#716b80]">₦12.5M Value</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="p-2 text-[#716b80] hover:text-primary dark:hover:text-white hover:bg-primary/5 dark:hover:bg-zinc-700 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 2 -->
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Middle aged Nigerian man with glasses" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBDt3EW_OYpV9zXJ0TrPQzpAUW_BUGcVYJfIF0vUIjdMO6rp9au_DJtbjTNfvx0Ze7Fr-8FYM3mXnw9lUAAsYLMmOorwlJYwce6mQZcfYLmyOPxUhd3X0Op2hCz2tFglULr5cpEH6SIZCIfpgrmJHVobpojEX_z3FAToJuT8_K2UzBOD1IMbojBf3LePVW84gbQRYgD0l6EODnJjqp7lsISPuKIRLN2Xuss6fI_XKfXRqMAEPG9y8-uM35tuJfNpfYTYGA4bC_omQNf');"></div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-bold dark:text-white">Ibrahim Musa</p>
                                    <p class="text-xs text-[#716b80]">musa.vault@analyt.ng</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 rounded-full text-xs font-bold">Vault Manager</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-1.5">
                                <div class="size-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-medium dark:text-zinc-300">Active</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right text-sm text-[#716b80] dark:text-zinc-400">14 mins ago</td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-sm font-bold dark:text-white">0 Loans</p>
                            <p class="text-[10px] text-[#716b80]">Liquidity Ops</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="p-2 text-[#716b80] hover:text-primary dark:hover:text-white hover:bg-primary/5 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 3 -->
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Young professional woman profile" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCWzRsZL_kqdsTH42nwJDdCyiIq0aD9f4YSCsaJv4Bdicafh15jcTdanHt1EwKo4U1q9SWiDlIoNwhqrXvBz_jid0A9_yY08xQYKByiEc9XTDVv4oS5wZlmqeTTLVe2A8G68mnj1AGTcyyUzvRUnvZnB4N3B0_Xbfse70XlLx7q1YqQT-7JYdMjdzMLXoklTXl_cHHUaN66rUFH971WGRsnZPmQTXbFYH7x5lHYMo74Q6-QLLKOGHd8055e-qkA83hmWaTys6voN5tS');"></div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-bold dark:text-white">Fatimah Yusuf</p>
                                    <p class="text-xs text-[#716b80]">f.yusuf@analyt.ng</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 rounded-full text-xs font-bold">Credit Analyst</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-1.5">
                                <div class="size-2 rounded-full bg-amber-400"></div>
                                <span class="text-sm font-medium dark:text-zinc-300">Invited</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right text-sm text-[#716b80] dark:text-zinc-400">Never</td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-sm font-bold dark:text-white">0 Loans</p>
                            <p class="text-[10px] text-[#716b80]">Pending Setup</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="p-2 text-[#716b80] hover:text-primary dark:hover:text-white hover:bg-primary/5 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 4 -->
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center" data-alt="Young Nigerian man headshot" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDX3G8vsh9p6MMXE3BtZnVK2UV6ntz-jtONC0TfJZKTV37IxhrzB8l2ip7kj-Jw39gcHkBqXdIB82GpbxjXkJeTPZsGvEL81rhmnud7OGskGqUHDKs79uQ-PC_gvsPVH8Fo6GnDNYk4iq5vyFfnaAkcKhxBU__QuPDButqEc_Kq8fkBj6mXEZD4j2dFM0OU3jhnIID_yekctQyGXwxcX_oSAPV2DlqWwisDQIzNUSz33HdXOwdL_ThCNRhQK2AZFySXgDpRU9Ea6a2_');"></div>
                                <div class="flex flex-col">
                                    <p class="text-sm font-bold dark:text-white">Emeka Obi</p>
                                    <p class="text-xs text-[#716b80]">e.obi@analyt.ng</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full text-xs font-bold">Loan Officer</span>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-1.5">
                                <div class="size-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-medium dark:text-zinc-300">Active</span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right text-sm text-[#716b80] dark:text-zinc-400">5 hours ago</td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-sm font-bold dark:text-white">18 Loans</p>
                            <p class="text-[10px] text-[#716b80]">₦4.2M Value</p>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="p-2 text-[#716b80] hover:text-primary dark:hover:text-white hover:bg-primary/5 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination/Footer -->
            <div class="px-6 py-4 border-t border-[#dfdee3] dark:border-zinc-800 flex items-center justify-between">
                <p class="text-sm text-[#716b80] font-medium">Showing 4 of 12 members</p>
                <div class="flex gap-2">
                    <button class="px-4 py-2 border border-[#dfdee3] dark:border-zinc-800 rounded-lg text-sm font-bold text-primary dark:text-white hover:bg-background-light dark:hover:bg-zinc-800 transition-colors">Previous</button>
                    <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary/90 transition-colors">Next</button>
                </div>
            </div>
        </div>
    </div>
    <div x-show="showInviteMemberModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center modal-overlay px-4" style="display: none;">
        <x-invite-member-modal />
    </div>
</x-app-layout>

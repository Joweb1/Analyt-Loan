<x-app-layout>
    <div class="px-8 pt-6 pb-2 shrink-0">
        <div class="flex items-center gap-2 text-xs font-semibold text-[#606e8a] uppercase tracking-wider mb-2">
            <a class="hover:text-primary transition-colors" href="#">Analyt Loan</a>
            <span>/</span>
            <a class="hover:text-primary transition-colors" href="#">Loan Management</a>
            <span>/</span>
            <span class="text-[#111318] dark:text-white">Kanban Board</span>
        </div>
        <div class="flex justify-between items-end">
            <div>
                <h2 class="text-3xl font-extrabold text-[#111318] dark:text-white tracking-tight">Nigerian Portfolio</h2>
                <p class="text-[#606e8a] text-sm mt-1">Total Active Pipeline: ₦124,500,000</p>
            </div>
            <div class="flex gap-2 mb-1">
                <div class="flex p-1 bg-white dark:bg-white/5 rounded-2xl border border-[#dbdee6]">
                    <button class="px-3 py-1.5 text-xs font-bold rounded-xl bg-primary text-white">Board</button>
                    <button class="px-3 py-1.5 text-xs font-bold rounded-xl text-[#606e8a] hover:bg-background-light dark:hover:bg-white/10">List View</button>
                </div>
                <button class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-white/5 border border-[#dbdee6] rounded-2xl text-sm font-bold text-[#111318] dark:text-white hover:bg-background-light transition-colors">
                    <span class="material-symbols-outlined text-lg">filter_list</span>
                    Filters
                </button>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-x-auto p-8 pt-4 custom-scrollbar">
        <div class="flex gap-6 h-full min-w-max">
            <div class="kanban-column flex flex-col gap-4">
                <div class="flex items-center justify-between border-b-2 border-primary pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide">APPLICATION</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">12</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">Total: ₦4,800,000</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 custom-scrollbar">
                    <div class="bg-white dark:bg-[#1c2433] p-4 card-radius shadow-sm border border-[#dbdee6] dark:border-white/5 hover:shadow-md transition-shadow cursor-pointer group">
                        <div class="flex justify-between items-start mb-3">
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">Low Risk</span>
                            <span class="material-symbols-outlined text-gray-300 group-hover:text-primary text-lg transition-colors">more_horiz</span>
                        </div>
                        <p class="text-sm font-extrabold dark:text-white mb-1">Adewale Adebayo</p>
                        <p class="text-lg font-black text-primary dark:text-slate-200 mb-3">₦2,500,000.00</p>
                        <div class="flex items-center gap-2 text-[#606e8a] bg-background-light dark:bg-white/5 p-2 rounded-xl">
                            <span class="material-symbols-outlined text-sm">laptop_mac</span>
                            <span class="text-[11px] font-bold">MacBook Pro M2 Max</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="size-6 rounded-full border-2 border-white dark:border-[#1c2433] bg-cover" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCJFZJ-Wvqv258V9uFkEhDlDxPwxhu1rUodx102UP77e0blj1kJSD02eUT5RvsMk0QDQNMh_2iizRCHepemqLQsTxuZd9pzQKCN4kqYMfhnYqPLfhUFezfaTw2hzFr5Nwg6i2Fko4McWKyNwh5GmmFwgFWVGvaGd4SRTKsYLPBO_hongCOlcF-gxhFFlUoSqZpfIRAyjUax6V0YY0nFv3VFosMSkxEcdEsY1YeyCxZ2b2yNMG-S8SGMn6RdfhFg3k8zoh9yV6AQ0-U');"></div>
                            <p class="text-[10px] text-[#606e8a] font-medium">Updated 2h ago</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#1c2433] p-4 card-radius shadow-sm border border-[#dbdee6] dark:border-white/5 hover:shadow-md transition-shadow cursor-pointer">
                        <div class="flex justify-between items-start mb-3">
                            <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">Medium Risk</span>
                            <span class="material-symbols-outlined text-gray-300 text-lg">more_horiz</span>
                        </div>
                        <p class="text-sm font-extrabold dark:text-white mb-1">Ngozi Obi</p>
                        <p class="text-lg font-black text-primary dark:text-slate-200 mb-3">₦1,200,000.00</p>
                        <div class="flex items-center gap-2 text-[#606e8a] bg-background-light dark:bg-white/5 p-2 rounded-xl">
                            <span class="material-symbols-outlined text-sm">smartphone</span>
                            <span class="text-[11px] font-bold">iPhone 13 Pro Max</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="size-6 rounded-full border-2 border-white dark:border-[#1c2433] bg-cover" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuA10OQHdc6eJ9WGZeEhqhxafLuW3CFakZudXlhoaTTAlCakHl_PwGBqwAmNdcxY83ffT6-0_psEVZlHDld-Mex2TOO9uVabx7WJ2NXHIMgasf2mNLACVvAEU7WSo9li91kBuKddY3R81MDzgGglxOHe0Ys_i83RH_REZn_O1fXebbvfvKMiHZhpi-E4hcys_av9wMchoVkWxWAL7c-TtcAEccutpOyCS4Tzjhkvy3EGKqmb8yDvWVXj1QesLJUpvRyuoUcw4_niUNg');"></div>
                            <p class="text-[10px] text-[#606e8a] font-medium">Updated 5h ago</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kanban-column flex flex-col gap-4">
                <div class="flex items-center justify-between border-b-2 border-purple-400 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide">VERIFICATION</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">8</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">Total: ₦12,450,000</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 custom-scrollbar">
                    <div class="bg-white dark:bg-[#1c2433] p-4 card-radius shadow-sm border border-[#dbdee6] dark:border-white/5 hover:shadow-md transition-shadow cursor-pointer">
                        <div class="flex justify-between items-start mb-3">
                            <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">High Risk</span>
                            <span class="material-symbols-outlined text-gray-300 text-lg">more_horiz</span>
                        </div>
                        <p class="text-sm font-extrabold dark:text-white mb-1">Chinedu Okafor</p>
                        <p class="text-lg font-black text-primary dark:text-slate-200 mb-3">₦8,500,000.00</p>
                        <div class="flex items-center gap-2 text-[#606e8a] bg-background-light dark:bg-white/5 p-2 rounded-xl">
                            <span class="material-symbols-outlined text-sm">directions_car</span>
                            <span class="text-[11px] font-bold">Toyota Corolla 2018</span>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <div class="size-6 rounded-full border-2 border-white dark:border-[#1c2433] bg-cover" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCnRoZfLraXOgnAKJurCZwCYj4EiJZcl5YGxlJgA-XuObKdkNpTnp-181vj5DcnHNTNl4BGVK99CY7MlhGZLG9Mu80VkVWXe4MgIOCT0AXotJzOU4woKBwMNoYpVUGKrm4mXIbru0ZrP41Qrz7C20-6DpVFQhS_jY3uA3bcwr8CRSLuE5IkyPCMh_bQUs3CG11Cj2JPYWH2dypACGQJWVn4n9VlnmqE77xKUD4nU6TeUbYxY2PFYaZG0RrSTvH3i2OstNNSwwdwSOY');"></div>
                            <p class="text-[10px] text-red-500 font-bold uppercase tracking-tighter">Missing BVN</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kanban-column flex flex-col gap-4">
                <div class="flex items-center justify-between border-b-2 border-orange-400 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide">APPROVAL</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">4</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">Total: ₦2,400,000</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 custom-scrollbar">
                    <div class="border-2 border-dashed border-[#dbdee6] card-radius h-40 flex items-center justify-center p-6 text-center">
                        <p class="text-[10px] text-[#606e8a] font-bold uppercase tracking-widest leading-relaxed">Credit scoring in progress...</p>
                    </div>
                </div>
            </div>
            <div class="kanban-column flex flex-col gap-4">
                <div class="flex items-center justify-between border-b-2 border-green-400 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide">ACTIVE</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">145</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">Total: ₦85,400,000</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 custom-scrollbar">
                    <div class="bg-white dark:bg-[#1c2433] p-4 card-radius shadow-sm border border-[#dbdee6] dark:border-white/5 hover:shadow-md transition-shadow cursor-pointer border-l-4 border-l-green-500">
                        <div class="flex justify-between items-start mb-3">
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">On Time</span>
                            <span class="material-symbols-outlined text-gray-300 text-lg">check_circle</span>
                        </div>
                        <p class="text-sm font-extrabold dark:text-white mb-1">Tunde Folayan</p>
                        <p class="text-lg font-black text-primary dark:text-slate-200 mb-3">₦4,200,000.00</p>
                        <div class="flex items-center gap-2 text-[#606e8a] bg-background-light dark:bg-white/5 p-2 rounded-xl">
                            <span class="material-symbols-outlined text-sm">apartment</span>
                            <span class="text-[11px] font-bold">Shop Expansion Loan</span>
                        </div>
                        <div class="mt-4">
                            <div class="w-full h-1 bg-background-light dark:bg-white/10 rounded-full mb-1">
                                <div class="bg-green-500 h-1 rounded-full" style="width: 75%;"></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold uppercase tracking-tight">
                                <span class="text-green-500">9/12 Paid</span>
                                <span class="text-[#606e8a]">Next: Oct 15</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kanban-column flex flex-col gap-4">
                <div class="flex items-center justify-between border-b-2 border-[#dbdee6] pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide text-[#606e8a]">CLOSED</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">89</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">Total: ₦19,450,000</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 opacity-60 custom-scrollbar">
                    <div class="bg-white dark:bg-[#1c2433] p-4 card-radius shadow-sm border border-[#dbdee6] dark:border-white/5 hover:shadow-md transition-shadow cursor-pointer grayscale">
                        <div class="flex justify-between items-start mb-3">
                            <span class="bg-gray-100 text-gray-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">Repaid</span>
                            <span class="material-symbols-outlined text-green-500 text-lg">verified</span>
                        </div>
                        <p class="text-sm font-extrabold dark:text-white mb-1">Amina Ibrahim</p>
                        <p class="text-lg font-black text-[#606e8a] mb-3">₦950,000.00</p>
                        <div class="flex items-center gap-2 text-[#606e8a] bg-background-light dark:bg-white/5 p-2 rounded-xl">
                            <span class="material-symbols-outlined text-sm">watch</span>
                            <span class="text-[11px] font-bold">Apple Watch Ultra</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="fixed bottom-8 right-24 flex items-center gap-3 px-6 py-4 bg-primary text-white rounded-full shadow-2xl shadow-primary/40 hover:scale-105 transition-transform active:scale-95 z-50">
        <span class="material-symbols-outlined font-bold">add</span>
        <span class="text-base font-bold tracking-wide">New Loan Application</span>
    </button>
</x-app-layout>

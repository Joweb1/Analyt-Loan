<x-app-layout>
    <div class="max-w-6xl mx-auto px-2 py-2 w-full">
        <!-- Section Header & Filters -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h2 class="text-[#111318] dark:text-white text-[28px] font-bold tracking-tight">Customer Directory</h2>
                <p class="text-[#606b8a] text-sm mt-1">Manage 1,248 active borrowers</p>
            </div>
            <div class="flex gap-3 items-center">
                <button class="flex h-10 shrink-0 items-center justify-center gap-x-2 rounded-xl bg-white dark:bg-[#1a1f2e] border border-[#e5e7eb] dark:border-[#2d3344] px-4 shadow-sm hover:bg-[#f9fafb] transition-colors">
                    <span class="text-[#111318] dark:text-white text-sm font-medium">Region: Nigeria</span>
                    <span class="material-symbols-outlined text-[#606b8a] text-[20px]">expand_more</span>
                </button>
                <div class="h-8 w-[1px] bg-[#e5e7eb] dark:bg-[#2d3344] mx-1"></div>
                <div class="flex bg-white dark:bg-[#1a1f2e] border border-[#e5e7eb] dark:border-[#2d3344] rounded-xl p-1 shadow-sm">
                    <button class="p-1.5 rounded-lg bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-[20px]">grid_view</span>
                    </button>
                    <button class="p-1.5 rounded-lg text-[#606b8a] hover:bg-[#f0f1f5] dark:hover:bg-[#2d3344]">
                        <span class="material-symbols-outlined text-[20px]">list</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full max-w-[600px] mx-auto my-8">
            <a href="{{ route('customer.create') }}" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary text-white rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-base">person_add</span> Add New Customer/Borrower
                </a>        </div>
        <!-- Grid of Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Borrower Card 1 -->
            <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-12 rounded-full bg-cover bg-center border-2 border-white dark:border-[#2d3344]" data-alt="Portrait of Chinedu Adewale borrower profile" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAM4PiPKOygvYWbfU2oUeml1Sxq59PVrN-ZH-supV21yjt1RjLekL-PZt2xGilchGahBUxt6_8ywbJtyG_dfeNnLu0Ho5eDWl-t95yCJytEbuSP8gmv_XxpbgLhBz0qQlaK6X3oAqhNH36cRzq_afxgJ0PWmyRItnW_eMcU2WKEsSiNnSZfH9kEVDT7iRlBVWI2D06JsUxIlZ65slGppLSfY-M1FobHVS0GC_LlKKJs0X4_zPgiGOJOIerjhSPl1-UaVCvxNbS5pUY')"></div>
                    <div>
                        <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight">Chinedu Adewale</h3>
                        <p class="text-[#606b8a] text-xs">Lagos, NG</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p>
                        <p class="text-[#111318] dark:text-white text-xl font-bold">₦1,250,000.00</p>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Repayment Score</p>
                            <span class="px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-[10px] font-bold uppercase">Low Risk</span>
                        </div>
                        <div class="relative size-12">
                            <svg class="size-full -rotate-90" viewbox="0 0 36 36">
                                <circle class="stroke-[#f0f1f5] dark:stroke-[#2d3344]" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle>
                                <circle class="stroke-primary" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100" stroke-dashoffset="15" stroke-linecap="round" stroke-width="3"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[#111318] dark:text-white text-[10px] font-bold">85%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Quick Actions Overlay -->
                <div class="quick-actions absolute inset-x-0 bottom-0 bg-white/95 dark:bg-[#1a1f2e]/95 backdrop-blur-sm p-4 border-t border-[#e5e7eb] dark:border-[#2d3344] flex justify-between opacity-0 translate-y-2 transition-all duration-300">
                    <button class="size-10 rounded-lg bg-primary text-white flex items-center justify-center hover:bg-blue-700 transition-colors" title="Issue Loan">
                        <span class="material-symbols-outlined text-[20px]">add_card</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center hover:bg-[#e2e4e9] transition-colors" title="Send Message">
                        <span class="material-symbols-outlined text-[20px]">chat_bubble</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center hover:bg-[#e2e4e9] transition-colors" title="View Profile">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                </div>
            </div>
            <!-- Borrower Card 2 -->
            <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-12 rounded-full bg-cover bg-center border-2 border-white dark:border-[#2d3344]" data-alt="Portrait of Amaka Eniola borrower profile" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAHhTBzGwzejCC_BYK9URQNDshLVQu_cUeZLJlkA61AGIMjb3YF5sb9WZM1iFEuqCxmL9_XaKHjImhEPLEKrjKUOPgbys82ZhKTVoVa6yRUyat_4mVSR1exL_sokP5vFuVC48-8XAdDm7KIkGUP3520O8BLs7SaWzzanxz5Qby90twOoWY1ewAJDI_vhfEWa0E-Fa3VCFsGaGJDJMmhpnQcJw_xJSOWfuHHuz2oZAXjzX5kSM6cv7s7TyK6bKsHm_u0RMFX_dJB1RQ')"></div>
                    <div>
                        <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight">Amaka Eniola</h3>
                        <p class="text-[#606b8a] text-xs">Abuja, NG</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p>
                        <p class="text-[#111318] dark:text-white text-xl font-bold">₦850,000.00</p>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Repayment Score</p>
                            <span class="px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-[10px] font-bold uppercase">Exceptional</span>
                        </div>
                        <div class="relative size-12">
                            <svg class="size-full -rotate-90" viewbox="0 0 36 36">
                                <circle class="stroke-[#f0f1f5] dark:stroke-[#2d3344]" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle>
                                <circle class="stroke-primary" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100" stroke-dashoffset="8" stroke-linecap="round" stroke-width="3"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[#111318] dark:text-white text-[10px] font-bold">92%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="quick-actions absolute inset-x-0 bottom-0 bg-white/95 dark:bg-[#1a1f2e]/95 backdrop-blur-sm p-4 border-t border-[#e5e7eb] dark:border-[#2d3344] flex justify-between opacity-0 translate-y-2 transition-all duration-300">
                    <button class="size-10 rounded-lg bg-primary text-white flex items-center justify-center" title="Issue Loan">
                        <span class="material-symbols-outlined text-[20px]">add_card</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center" title="Send Message">
                        <span class="material-symbols-outlined text-[20px]">chat_bubble</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center" title="View Profile">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                </div>
            </div>
            <!-- Borrower Card 3 -->
            <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-12 rounded-full bg-cover bg-center border-2 border-white dark:border-[#2d3344]" data-alt="Portrait of Tunde Folawiyo borrower profile" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDdiNU675RAd-qOqS5ohE0n5-bEQquh5TBB1P79Ee1uL7ReiEePCRJN9EDj9lv-Zxu2rqPQPFjjVyQFdgqIGFtTnYScJtDNiKxaWOD-Ttp60QQaHIGWTYOjBCkyKNWkQXnLFt9Fuw8Efd12M48918lpydo8zUKux8MocCLoE0-NFvxbTCfzyEiSXs1ejR8s98Tz-aYsGD124bYxQW16X4KrUIsvFtHNFiHcByhytp2P6Ao1MFsubRawvIWqwfYQE46j6Yudq5U_EYw')"></div>
                    <div>
                        <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight">Tunde Folawiyo</h3>
                        <p class="text-[#606b8a] text-xs">Ibadan, NG</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p>
                        <p class="text-[#111318] dark:text-white text-xl font-bold">₦2,100,000.00</p>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Repayment Score</p>
                            <span class="px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-[10px] font-bold uppercase">High Risk</span>
                        </div>
                        <div class="relative size-12">
                            <svg class="size-full -rotate-90" viewbox="0 0 36 36">
                                <circle class="stroke-[#f0f1f5] dark:stroke-[#2d3344]" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle>
                                <circle class="stroke-red-500" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100" stroke-dashoffset="55" stroke-linecap="round" stroke-width="3"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[#111318] dark:text-white text-[10px] font-bold">45%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="quick-actions absolute inset-x-0 bottom-0 bg-white/95 dark:bg-[#1a1f2e]/95 backdrop-blur-sm p-4 border-t border-[#e5e7eb] dark:border-[#2d3344] flex justify-between opacity-0 translate-y-2 transition-all duration-300">
                    <button class="size-10 rounded-lg bg-primary text-white flex items-center justify-center" title="Issue Loan">
                        <span class="material-symbols-outlined text-[20px]">add_card</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center" title="Send Message">
                        <span class="material-symbols-outlined text-[20px]">chat_bubble</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center" title="View Profile">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                </div>
            </div>
            <!-- Borrower Card 4 -->
            <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-12 rounded-full bg-cover bg-center border-2 border-white dark:border-[#2d3344]" data-alt="Portrait of Chioma Adeyemi borrower profile" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuB60Zu-ymMYA08jd5oeN7d94amb_hKFbqPfTD6o0hjMcq9A5gLTwEew1ZgSCR_B-d2JD67stkK2LeLlXG-SK9ba43WAWPxYJOaOz3BrE11OzsZloVHXKhQXW8U-JvvyDQ3wny00wlcuxpEZ2c0A8h_-RdJspoulEmWJcgDmwSXISwnr4ij8V8lbiM15bMzvc_HYMamrhjcFKjeOizmWluF1QKRgvF2qTVb2vygBMGtGoYlcnEWMJQZSuUXFfNUFJvSA2prPe1_p958')"></div>
                    <div>
                        <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight">Chioma Adeyemi</h3>
                        <p class="text-[#606b8a] text-xs">Enugu, NG</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p>
                        <p class="text-[#111318] dark:text-white text-xl font-bold">₦420,000.00</p>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <div>
                            <p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Repayment Score</p>
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[10px] font-bold uppercase">Medium Risk</span>
                        </div>
                        <div class="relative size-12">
                            <svg class="size-full -rotate-90" viewbox="0 0 36 36">
                                <circle class="stroke-[#f0f1f5] dark:stroke-[#2d3344]" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle>
                                <circle class="stroke-amber-500" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100" stroke-dashoffset="22" stroke-linecap="round" stroke-width="3"></circle>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[#111318] dark:text-white text-[10px] font-bold">78%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="quick-actions absolute inset-x-0 bottom-0 bg-white/95 dark:bg-[#1a1f2e]/95 backdrop-blur-sm p-4 border-t border-[#e5e7eb] dark:border-[#2d3344] flex justify-between opacity-0 translate-y-2 transition-all duration-300">
                    <button class="size-10 rounded-lg bg-primary text-white flex items-center justify-center" title="Issue Loan">
                        <span class="material-symbols-outlined text-[20px]">add_card</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center" title="Send Message">
                        <span class="material-symbols-outlined text-[20px]">chat_bubble</span>
                    </button>
                    <button class="size-10 rounded-lg bg-[#f0f1f5] dark:bg-[#2d3344] text-[#111318] dark:text-white flex items-center justify-center" title="View Profile">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                </div>
            </div>
            <!-- Repeat cards to fill grid -->
            <!-- Simulating more data -->
            <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-12 rounded-full bg-[#f0f1f5] dark:bg-[#2d3344] flex items-center justify-center">
                        <span class="text-[#606b8a] font-bold">OO</span>
                    </div>
                    <div>
                        <h3 class="text-[#111318] dark:text-white font-bold text-base leading-tight">Olumide Oworu</h3>
                        <p class="text-[#606b8a] text-xs">Port Harcourt, NG</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div><p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold">Total Debt</p><p class="text-[#111318] dark:text-white text-xl font-bold">₦15,000.00</p></div>
                    <div class="flex items-center justify-between pt-2">
                        <div><p class="text-[#606b8a] text-[11px] uppercase tracking-wider font-semibold mb-1">Repayment Score</p><span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-[10px] font-bold uppercase">New User</span></div>
                        <div class="relative size-12">
                            <svg class="size-full -rotate-90" viewbox="0 0 36 36"><circle class="stroke-[#f0f1f5] dark:stroke-[#2d3344]" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle><circle class="stroke-primary" cx="18" cy="18" fill="none" r="16" stroke-dasharray="100" stroke-dashoffset="100" stroke-linecap="round" stroke-width="3"></circle></svg>
                            <div class="absolute inset-0 flex items-center justify-center"><span class="text-[#111318] dark:text-white text-[10px] font-bold">-%</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="group relative bg-white dark:bg-[#1a1f2e] p-5 rounded-xl border border-[#e5e7eb] dark:border-[#2d3344] shadow-sm hover:shadow-xl transition-all duration-300 custom-card-hover overflow-hidden opacity-50 border-dashed">
                <div class="flex flex-col items-center justify-center h-full gap-2 py-10">
                    <span class="material-symbols-outlined text-[#606b8a] text-[40px]">person_add</span>
                    <p class="text-sm font-medium text-[#606b8a]">Add New Borrower</p>
                </div>
            </div>
        </div>
        <!-- Pagination Footer -->
        <div class="mt-12 flex items-center justify-between border-t border-[#e5e7eb] dark:border-[#2d3344] pt-6 pb-12">
            <p class="text-sm text-[#606b8a]">Showing 1 to 12 of 1,248 borrowers</p>
            <div class="flex gap-2">
                <button class="px-4 py-2 text-sm font-medium rounded-lg border border-[#e5e7eb] dark:border-[#2d3344] text-[#606b8a] hover:bg-white dark:hover:bg-[#1a1f2e] transition-colors">Previous</button>
                <button class="px-4 py-2 text-sm font-medium rounded-lg border border-[#e5e7eb] dark:border-[#2d3344] text-[#606b8a] hover:bg-white dark:hover:bg-[#1a1f2e] transition-colors">Next</button>
            </div>
        </div>
    </div>
</x-app-layout>

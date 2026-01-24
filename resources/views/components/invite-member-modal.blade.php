<div class="fixed inset-0 z-50 flex items-center justify-center modal-overlay px-4">
    <!-- Modal Card -->
    <div class="bg-white dark:bg-slate-900 w-full max-w-[520px] rounded-lg shadow-2xl overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="px-8 pt-8 pb-4 flex justify-between items-start">
            <div class="text-left">
                <h2 class="text-primary dark:text-white tracking-tight text-[26px] font-extrabold leading-tight">Invite Team Member</h2>
                <p class="text-[#6b7180] dark:text-slate-400 text-sm font-medium leading-normal mt-1">Grant your team access to Analyt Loan 2.0</p>
            </div>
            <button @click="showInviteMemberModal = false" class="text-slate-400 hover:text-primary transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <!-- Modal Content (Form) -->
        <div class="px-8 py-4 space-y-5 overflow-y-auto max-h-[75vh]">
            <!-- Full Name Input -->
            <div class="flex flex-col gap-1.5">
                <label class="text-primary dark:text-slate-200 text-sm font-semibold px-1">Full Name</label>
                <div class="relative">
                    <input class="form-input flex w-full rounded-full text-primary dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-primary/20 border border-slate-200 dark:border-slate-700 h-12 placeholder:text-slate-400 px-5 text-sm font-normal" placeholder="e.g. Chinua Achebe" type="text"/>
                </div>
            </div>
            <!-- Email Address Input -->
            <div class="flex flex-col gap-1.5">
                <label class="text-primary dark:text-slate-200 text-sm font-semibold px-1">Email Address</label>
                <div class="relative">
                    <input class="form-input flex w-full rounded-full text-primary dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-primary/20 border border-slate-200 dark:border-slate-700 h-12 placeholder:text-slate-400 px-5 text-sm font-normal" placeholder="name@company.ng" type="email"/>
                </div>
            </div>
            <!-- Role Selection -->
            <div class="flex flex-col gap-1.5">
                <label class="text-primary dark:text-slate-200 text-sm font-semibold px-1">Select Role</label>
                <div class="relative">
                    <select class="form-select appearance-none flex w-full rounded-full text-primary dark:text-white dark:bg-slate-800 focus:ring-2 focus:ring-primary/20 border border-slate-200 dark:border-slate-700 h-12 px-5 text-sm font-normal pr-10">
                        <option value="officer">Loan Officer</option>
                        <option value="admin">Admin</option>
                        <option value="vault">Vault Manager</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                        <span class="material-symbols-outlined text-slate-400">expand_more</span>
                    </div>
                </div>
            </div>
            <!-- Permissions Preview Section -->
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-5 border border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 px-1">Permissions Preview</h3>
                <div class="space-y-4">
                    <!-- Permission Item 1 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center text-primary dark:text-slate-200 shadow-sm border border-slate-100 dark:border-slate-700">
                                <span class="material-symbols-outlined text-base">check_circle</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-primary dark:text-slate-200">Can Approve Loans</p>
                                <p class="text-[11px] text-slate-500">Authorize pending loan requests</p>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <div class="w-10 h-5 bg-primary rounded-full"></div>
                            <div class="absolute left-[22px] top-[2.5px] bg-white w-[15px] h-[15px] rounded-full transition-all"></div>
                        </div>
                    </div>
                    <!-- Permission Item 2 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center text-primary dark:text-slate-200 shadow-sm border border-slate-100 dark:border-slate-700">
                                <span class="material-symbols-outlined text-base">lock_open</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-primary dark:text-slate-200">Can Access Vault</p>
                                <p class="text-[11px] text-slate-500">View liquidity and reserve balances</p>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer opacity-50">
                            <div class="w-10 h-5 bg-slate-300 dark:bg-slate-600 rounded-full"></div>
                            <div class="absolute left-[2.5px] top-[2.5px] bg-white w-[15px] h-[15px] rounded-full transition-all"></div>
                        </div>
                    </div>
                    <!-- Permission Item 3 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center text-primary dark:text-slate-200 shadow-sm border border-slate-100 dark:border-slate-700">
                                <span class="material-symbols-outlined text-base">file_download</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-primary dark:text-slate-200">Can Export Data</p>
                                <p class="text-[11px] text-slate-500">Download CSV and PDF reports</p>
                            </div>
                        </div>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <div class="w-10 h-5 bg-primary rounded-full"></div>
                            <div class="absolute left-[22px] top-[2.5px] bg-white w-[15px] h-[15px] rounded-full transition-all"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Footer -->
        <div class="px-8 pb-8 pt-4">
            <button class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-full transition-all shadow-lg flex items-center justify-center gap-2 group">
                <span>Send Invitation</span>
                <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">send</span>
            </button>
            <button @click="showInviteMemberModal = false" class="w-full mt-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm font-semibold py-2 transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>
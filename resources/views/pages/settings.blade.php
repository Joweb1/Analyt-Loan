<x-app-layout>
    @section('title', 'Premium System Settings')
    <div class="max-w-6xl mx-auto">
        <!-- Page Heading -->
        <div class="mb-8">
            <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">General Settings</h1>
            <p class="text-gray-500 mt-1">Manage your organization profile, regional localization, and global loan configurations.</p>
        </div>
        <div class="grid grid-cols-12 gap-8">
            <!-- Settings Sub-Navigation -->
            <nav class="col-span-12 lg:col-span-3 flex flex-col gap-1">
                <a class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-l-4 border-accent-blue rounded-r-lg shadow-sm" href="#">
                    <span class="text-accent-blue dark:text-white font-bold text-sm">General</span>
                    <span class="material-symbols-outlined text-accent-blue dark:text-white text-[18px]">chevron_right</span>
                </a>
                <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-accent-blue transition-colors" href="#">
                    <span class="font-semibold text-sm">Security</span>
                </a>
                <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-accent-blue transition-colors" href="{{ route('settings.team-members') }}">
                    <span class="font-semibold text-sm">Team Members</span>
                </a>
                <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-accent-blue transition-colors" href="#">
                    <span class="font-semibold text-sm">Notifications</span>
                </a>
                <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-accent-blue transition-colors" href="#">
                    <span class="font-semibold text-sm">API &amp; Integrations</span>
                </a>
            </nav>
            <!-- Main Settings Card -->
            <div class="col-span-12 lg:col-span-9 space-y-6">
                <!-- Section: Organization Profile -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Organization Profile</h2>
                    <div class="space-y-6">
                        <div class="flex items-center gap-6 pb-6 border-b border-gray-100 dark:border-gray-700">
                            <div class="relative group">
                                <div class="w-20 h-20 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300">
                                    <span class="material-symbols-outlined text-gray-400">add_a_photo</span>
                                </div>
                                <div class="absolute inset-0 bg-primary/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer rounded-xl">
                                    <span class="text-white text-xs font-bold uppercase tracking-wider">Change</span>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-primary dark:text-white">Organization Logo</h4>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG or SVG. Max 2MB (Recommended 400x400px).</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Bank Name</label>
                                <input class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm" type="text" value="Analyt Financial Services Ltd"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Registration Number (RC)</label>
                                <input class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm" type="text" value="RC-92834012"/>
                            </div>
                            <div class="flex flex-col gap-2 md:col-span-2">
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Registered Office Address</label>
                                <textarea class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm" rows="3">12B Admiralty Way, Lekki Phase 1, Lagos, Nigeria</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Section: Regional Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Regional Settings</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">System Currency</label>
                            <div class="relative">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">₦</div>
                                <select class="w-full pl-10 pr-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm appearance-none">
                                    <option selected="">Nigerian Naira (NGN)</option>
                                    <option>US Dollar (USD)</option>
                                    <option>British Pound (GBP)</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-bold text-gray-700 dark:text-gray-300">System Timezone</label>
                            <select class="w-full px-4 py-2.5 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm">
                                <option selected="">(GMT+01:00) West Central Africa (Lagos)</option>
                                <option>(GMT+00:00) Casablanca, Monrovia, Reykjavik</option>
                                <option>(GMT+02:00) Cairo, Harare, Pretoria</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- Section: Loan Preferences -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-primary dark:text-white">Loan Preferences</h2>
                        <span class="bg-green-100 text-green-700 text-[10px] uppercase font-bold px-2 py-0.5 rounded">Active Ruleset</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Default Interest Rate (Monthly)</label>
                                <div class="relative">
                                    <input class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm pr-10" step="0.1" type="number" value="4.5"/>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">%</div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Grace Period (Repayments)</label>
                                <div class="flex gap-2">
                                    <input class="w-20 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm" type="number" value="3"/>
                                    <select class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 focus:ring-accent-blue focus:border-accent-blue text-sm">
                                        <option selected="">Days</option>
                                        <option>Weeks</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-5">
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <div>
                                    <p class="text-sm font-bold text-primary dark:text-white">Auto-approval for Gold Clients</p>
                                    <p class="text-xs text-gray-500">Bypass manual review for scores &gt; 750</p>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input checked="" class="sr-only peer" type="checkbox"/>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-blue"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                <div>
                                    <p class="text-sm font-bold text-primary dark:text-white">Penalty Auto-calculation</p>
                                    <p class="text-xs text-gray-500">Apply late fees automatically after grace period</p>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input checked="" class="sr-only peer" type="checkbox"/>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-blue"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sticky Mobile Save Bar placeholder -->
                <div class="flex justify-end gap-4 pt-4 lg:pb-12">
                    <button class="px-6 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold rounded-xl text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-800">
                        Discard
                    </button>
                    <button class="px-8 py-2.5 bg-accent-blue text-white font-bold rounded-xl text-sm shadow-lg shadow-accent-blue/20 hover:shadow-accent-blue/30 active:scale-95 transition-all">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Notification Settings</h1>
        <p class="text-gray-500 mt-1">Choose how you want to be notified and automate borrower nudges.</p>
    </div>

    <div class="grid grid-cols-12 gap-8">
        <nav class="col-span-12 lg:col-span-3 flex flex-col gap-1">
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings') }}">
                <span class="font-semibold text-sm">General</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.security') }}">
                <span class="font-semibold text-sm">Security</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.team-members') }}">
                <span class="font-semibold text-sm">Team Members</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.roles') }}">
                <span class="font-semibold text-sm">Roles & Permissions</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 text-gray-500 hover:text-primary transition-colors" href="{{ route('settings.form-builder') }}">
                <span class="font-semibold text-sm">Form Customization</span>
            </a>
            <a class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-l-4 border-primary rounded-r-lg shadow-sm" href="{{ route('settings.notifications') }}">
                <span class="text-primary dark:text-white font-bold text-sm">Notifications</span>
                <span class="material-symbols-outlined text-primary dark:text-white text-[18px]">chevron_right</span>
            </a>
        </nav>

        <div class="col-span-12 lg:col-span-9 space-y-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Automated Nudges</h2>
                
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                        <div>
                            <p class="text-sm font-bold text-primary dark:text-white">Email Payment Reminders</p>
                            <p class="text-xs text-gray-500">Automatically send "nudges" to borrowers with upcoming or overdue payments.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="email_reminders" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
                <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Staff Alerts</h2>
                
                <div class="space-y-6">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                        <div>
                            <p class="text-sm font-bold text-primary dark:text-white">Loan Approval Alerts</p>
                            <p class="text-xs text-gray-500">Notify admins when a new loan application requires review.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="loan_approval_alerts" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

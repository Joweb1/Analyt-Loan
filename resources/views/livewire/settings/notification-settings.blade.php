<div class="max-w-5xl mx-auto w-full">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Notification Settings</h1>
        <p class="text-gray-500 mt-1">Choose how you want to be notified and automate borrower nudges.</p>
    </div>

    <x-settings-nav active="notifications" />

    <div class="space-y-6">
        <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
            <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Staff & Admin Alerts</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-primary dark:text-white">Push Notifications (Web)</p>
                        <p class="text-xs text-gray-500">Enable or disable real-time browser push notifications for the entire organization.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="push_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-primary dark:text-white">Loan Approval Alerts</p>
                        <p class="text-xs text-gray-500">Notify staff when a new loan application requires review.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="loan_approval_alerts" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-primary dark:text-white">Repayment Alerts</p>
                        <p class="text-xs text-gray-500">Get notified immediately when a payment is logged.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="repayment_alerts" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-primary dark:text-white">New Borrower Alerts</p>
                        <p class="text-xs text-gray-500">Notify when a new customer completes registration.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="new_borrower_alerts" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-zinc-800">
            <h2 class="text-lg font-bold text-primary dark:text-white mb-6">Customer Automations</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-primary dark:text-white">Overdue Push Notifications</p>
                        <p class="text-xs text-gray-500">Automatically send push reminders to customers when their loans become overdue.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="overdue_alerts" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-800/50 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-primary dark:text-white">Email Nudges</p>
                        <p class="text-xs text-gray-500">Automatically send "nudges" to borrowers via email for upcoming payments.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="email_reminders" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

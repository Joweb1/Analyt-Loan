<div class="flex flex-col gap-8 max-w-4xl">
    <div>
        <h2 class="text-2xl font-bold text-slate-800 dark:text-white">Platform Settings</h2>
        <p class="text-slate-500 dark:text-slate-400">Configure global platform behavior and appearance.</p>
    </div>

    <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl shadow-soft border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="p-8 border-b border-slate-50 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white">General Configuration</h3>
        </div>
        <div class="p-8 flex flex-col gap-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Platform Brand Name</label>
                    <input wire:model="platformName" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider">Support Email</label>
                    <input wire:model="supportEmail" type="email" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20">
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Self-Service Registration</h4>
                        <p class="text-xs text-slate-500">Allow new organizations to register themselves.</p>
                    </div>
                    <button wire:click="toggleRegistration" class="w-12 h-6 {{ $allowNewRegistrations ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }} rounded-full relative transition-all">
                        <div class="absolute {{ $allowNewRegistrations ? 'right-1' : 'left-1' }} top-1 w-4 h-4 bg-white rounded-full transition-all"></div>
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white">Maintenance Mode</h4>
                        <p class="text-xs text-slate-500">Disable platform access for all organizations except App Owner.</p>
                    </div>
                    <button wire:click="toggleMaintenance" class="w-12 h-6 {{ $maintenanceMode ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-700' }} rounded-full relative transition-all">
                        <div class="absolute {{ $maintenanceMode ? 'right-1' : 'left-1' }} top-1 w-4 h-4 bg-white rounded-full transition-all"></div>
                    </button>
                </div>
            </div>
        </div>
        <div class="p-8 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700 flex justify-end">
            <button wire:click="saveSettings" class="px-8 py-3 bg-primary text-white rounded-xl font-bold shadow-lg shadow-primary/30 hover:scale-105 active:scale-95 transition-all">
                Save Global Settings
            </button>
        </div>
    </div>
</div>

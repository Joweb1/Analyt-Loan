<div x-data="{ open: @entangle('showModal') }" x-show="open" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-8 rounded-3xl shadow-2xl w-full max-w-lg transform transition-all animate-in zoom-in-95">
        <h2 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-widest mb-2 flex items-center gap-3">
            <span class="material-symbols-outlined text-3xl text-amber-500">warning</span>
            Mandatory Audit
        </h2>
        <p class="text-slate-500 dark:text-slate-400 font-medium mb-8 text-sm">Strict validation required to proceed. Please enter the figures recorded for the day.</p>

        <div class="space-y-4">
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Total Bank Deposit</label>
                <div class="relative">
                    <span class="absolute left-4 top-4 font-black text-slate-400">₦</span>
                    <input type="number" wire:model="bank_deposit" placeholder="0" class="w-full pl-8 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 font-bold text-lg text-slate-900 dark:text-white">
                </div>
                @error('bank_deposit') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-1">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Card</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3.5 font-black text-slate-400 text-sm">₦</span>
                        <input type="number" wire:model="card_payments" placeholder="0" class="w-full pl-7 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 font-bold text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Excess</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3.5 font-black text-slate-400 text-sm">₦</span>
                        <input type="number" wire:model="excess_cash" placeholder="0" class="w-full pl-7 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 font-bold text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
                <div class="col-span-1">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Physical</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3.5 font-black text-slate-400 text-sm">₦</span>
                        <input type="number" wire:model="physical_cash" placeholder="0" class="w-full pl-7 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 font-bold text-sm text-slate-900 dark:text-white">
                    </div>
                </div>
            </div>

            @error('shortfall_report')
                <div>
                    <label class="block text-[10px] font-black text-red-500 uppercase tracking-widest mb-1">Shortfall Reconciliation Report</label>
                    <textarea wire:model="shortfall_report" class="w-full bg-red-50 dark:bg-red-900/10 border-2 border-red-200 dark:border-red-900/30 rounded-xl p-4 font-medium text-slate-900 dark:text-white"></textarea>
                    <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                </div>
            @enderror
        </div>

        <button wire:click="validateAndClose" class="w-full mt-8 py-4 bg-primary text-white font-black uppercase tracking-widest rounded-xl shadow-lg shadow-primary/30 transition-all active:scale-95">
            Confirm & Validate Data
        </button>

        @if(auth()->user()->isAdmin())
            <button wire:click="override" class="w-full mt-4 py-2 bg-transparent text-red-600 font-bold uppercase tracking-widest border border-red-600 rounded-xl hover:bg-red-50 transition-all">
                Admin Override
            </button>
        @endif
    </div>
</div>

<div class="min-h-screen bg-white p-6 flex flex-col">
    <!-- Progress Header -->
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-bold text-brand bg-brand-soft px-2 py-1 rounded">Step 2 of 3</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Where should we send the money?</h1>
        <p class="text-slate-500 mt-1">Provide your primary bank account for disbursement.</p>
    </div>

    <form wire:submit="save" class="flex-1 flex flex-col gap-6">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Bank Name</label>
            <input type="text" wire:model="bank_name" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="e.g. GTBank">
            @error('bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Account Number</label>
            <input type="tel" wire:model="account_number" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="0123456789">
             @error('account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Account Name</label>
            <input type="text" wire:model="account_name" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="As it appears on your card">
             @error('account_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mt-10 pb-12">
            <button type="submit" class="w-full bg-brand text-white font-bold text-lg py-4 rounded-2xl shadow-xl shadow-brand/20 hover:opacity-90 hover:scale-[1.02] transition-all flex items-center justify-center gap-2">
                Next Step
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>
    </form>
</div>

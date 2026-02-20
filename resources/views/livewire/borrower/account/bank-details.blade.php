<div class="min-h-screen bg-white p-6 pb-32">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('borrower.account') }}" wire:navigate class="p-2 bg-slate-50 rounded-full text-slate-600">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Bank Details</h1>
    </div>

    <div class="bg-brand rounded-3xl p-6 shadow-lg text-white mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10">
            <span class="material-symbols-outlined text-8xl">account_balance</span>
        </div>
        <div class="relative z-10">
            <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-4">Current Disbursement Account</p>
            <h2 class="text-2xl font-bold mb-1">{{ $account_name ?: 'Not Set' }}</h2>
            <p class="text-lg font-mono tracking-widest opacity-90">{{ $account_number ?: '0000000000' }}</p>
            <div class="mt-4 inline-block px-3 py-1 rounded-lg bg-white/20 text-xs font-bold uppercase tracking-tighter">
                {{ $bank_name ?: 'No Bank Linked' }}
            </div>
        </div>
    </div>

    <div class="bg-brand-soft p-4 rounded-2xl mb-8 flex items-start gap-3">
        <span class="material-symbols-outlined text-brand mt-0.5">info</span>
        <p class="text-xs text-brand leading-relaxed font-medium">
            Update your details below if you wish to change your disbursement account.
        </p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Bank Name</label>
            <input type="text" wire:model="bank_name" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="e.g. Zenith Bank">
            @error('bank_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Account Number</label>
            <input type="tel" wire:model="account_number" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg font-mono tracking-wider" placeholder="0123456789">
            @error('account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-500 mb-1">Account Name</label>
            <input type="text" wire:model="account_name" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-lg" placeholder="Full Name on Account">
            @error('account_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-brand text-white font-bold py-4 rounded-xl shadow-lg shadow-brand/20 hover:opacity-90 transition-all">
            Update Bank Details
        </button>
    </form>
</div>

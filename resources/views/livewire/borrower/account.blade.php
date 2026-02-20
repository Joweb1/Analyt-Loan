<div class="min-h-screen bg-slate-50 p-6 pb-32">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Account</h1>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6 flex items-center gap-4">
        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-2xl font-bold text-slate-600">
            {{ substr($user->first_name, 0, 1) }}
        </div>
        <div>
            <h2 class="font-bold text-xl text-slate-900">{{ $user->name }}</h2>
            <p class="text-slate-500 text-sm">{{ $user->email }}</p>
        </div>
    </div>

    <div class="space-y-3">
        <a href="{{ route('borrower.account.personal-details') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-brand transition-colors">person</span>
                <span class="font-medium text-slate-700">Personal Details</span>
            </div>
            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
        </a>

        <a href="{{ route('borrower.account.bank-details') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-brand transition-colors">account_balance</span>
                <span class="font-medium text-slate-700">Bank Details</span>
            </div>
            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
        </a>
        
        <a href="{{ route('borrower.account.loan-agreements') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-brand transition-colors">description</span>
                <span class="font-medium text-slate-700">Loan Agreements</span>
            </div>
            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
        </a>
        
        <a href="{{ route('borrower.account.support') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 group-hover:text-brand transition-colors">headset_mic</span>
                <span class="font-medium text-slate-700">Help & Support</span>
            </div>
            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
        </a>
    </div>

    <div class="mt-8">
        <button wire:click="logout" class="w-full bg-red-50 text-red-600 font-bold py-4 rounded-xl border border-red-100 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">logout</span>
            Sign Out
        </button>
        <p class="text-center text-xs text-slate-400 mt-6">Version 2.0.0</p>
    </div>
</div>

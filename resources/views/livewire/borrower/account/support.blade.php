<div class="min-h-screen bg-slate-50 p-6 pb-32">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('borrower.account') }}" wire:navigate class="p-2 bg-white rounded-full text-slate-600 shadow-sm">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Help & Support</h1>
    </div>

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 mb-6">
        <div class="flex items-center gap-4 mb-6">
             @if($organization->logo_path)
                <img src="{{ Storage::url($organization->logo_path) }}" class="h-14 w-14 object-contain rounded-2xl bg-slate-50 p-2" alt="Logo">
            @else
                <div class="h-14 w-14 rounded-2xl bg-brand flex items-center justify-center text-white font-bold text-xl">
                    {{ substr($organization->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h2 class="font-bold text-slate-900">{{ $organization->name }}</h2>
                <p class="text-xs text-slate-500">Official Support Channel</p>
            </div>
        </div>

        <div class="space-y-4">
            <a href="mailto:{{ $organization->email }}" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 hover:bg-slate-100 transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-brand shadow-sm">
                    <span class="material-symbols-outlined">mail</span>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">Email Us</p>
                    <p class="text-sm font-bold text-slate-700">{{ $organization->email }}</p>
                </div>
            </a>

            <a href="tel:{{ $organization->phone }}" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 hover:bg-slate-100 transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-brand shadow-sm">
                    <span class="material-symbols-outlined">phone</span>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">Call Support</p>
                    <p class="text-sm font-bold text-slate-700">{{ $organization->phone }}</p>
                </div>
            </a>

            @if($organization->website)
            <a href="{{ $organization->website }}" target="_blank" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 hover:bg-slate-100 transition-colors group">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 group-hover:text-brand shadow-sm">
                    <span class="material-symbols-outlined">language</span>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">Visit Website</p>
                    <p class="text-sm font-bold text-slate-700">{{ str_replace(['http://', 'https://'], '', $organization->website) }}</p>
                </div>
            </a>
            @endif
        </div>
    </div>

    <h3 class="font-bold text-slate-900 mb-4 px-2">Frequently Asked Questions</h3>
    <div class="space-y-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-100">
            <p class="font-bold text-sm text-slate-800 mb-1">How do I increase my limit?</p>
            <p class="text-xs text-slate-500 leading-relaxed">Repay your loans on time and maintain a high trust score to automatically increase your credit limit.</p>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-100">
            <p class="font-bold text-sm text-slate-800 mb-1">When will I get my loan?</p>
            <p class="text-xs text-slate-500 leading-relaxed">Once approved, disbursements are usually processed within 5 to 30 minutes depending on your bank.</p>
        </div>
    </div>
</div>

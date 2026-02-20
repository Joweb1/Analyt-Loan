<div class="min-h-screen bg-slate-50 p-6 pb-32">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('borrower.account') }}" wire:navigate class="p-2 bg-white rounded-full text-slate-600 shadow-sm">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Loan Agreements</h1>
    </div>

    @if($loans->isEmpty())
        <div class="bg-white p-12 rounded-3xl text-center border border-slate-100">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-4">description</span>
            <h3 class="font-bold text-slate-900">No Agreements</h3>
            <p class="text-slate-500 text-sm">Once you have an active loan, your legal agreements will appear here.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($loans as $loan)
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loan #{{ $loan->loan_number }}</p>
                            <h3 class="text-lg font-black text-slate-900">₦{{ number_format($loan->amount) }}</h3>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-1 rounded uppercase bg-slate-100 text-slate-600">
                            {{ $loan->status }}
                        </span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('loan.print', $loan->id) }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors group">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-brand">download</span>
                                <span class="text-sm font-medium text-slate-700">Download Contract</span>
                            </div>
                            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                        </a>
                        
                        <a href="{{ route('schedule.print', $loan->id) }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors group">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-brand">calendar_month</span>
                                <span class="text-sm font-medium text-slate-700">Repayment Schedule</span>
                            </div>
                            <span class="material-symbols-outlined text-slate-300">chevron_right</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-8 p-4 bg-brand-soft rounded-2xl">
        <p class="text-[10px] text-brand uppercase font-black tracking-widest mb-2">Legal Note</p>
        <p class="text-xs text-brand leading-relaxed opacity-80">
            All loans are subject to the master lending agreement signed during onboarding. Defaulting on payments may result in legal action or impact your credit score.
        </p>
    </div>
</div>

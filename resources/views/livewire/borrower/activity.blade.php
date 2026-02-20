<div class="min-h-screen bg-slate-50 p-6 pb-32">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Activity</h1>

    <!-- Tabs -->
    <div class="flex p-1 bg-white rounded-xl shadow-sm border border-slate-100 mb-6">
        <button wire:click="setTab('loans')" class="flex-1 py-2 text-sm font-bold rounded-lg transition-all {{ $tab === 'loans' ? 'bg-slate-900 text-white shadow' : 'text-slate-500 hover:text-slate-900' }}">
            Loans
        </button>
        <button wire:click="setTab('repayments')" class="flex-1 py-2 text-sm font-bold rounded-lg transition-all {{ $tab === 'repayments' ? 'bg-slate-900 text-white shadow' : 'text-slate-500 hover:text-slate-900' }}">
            Repayments
        </button>
    </div>

    @if($tab === 'loans')
        <div class="space-y-4">
            @forelse($loans as $loan)
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $loan->loan_number }}</span>
                            <h3 class="font-black text-xl text-slate-900">₦{{ number_format($loan->amount, 2) }}</h3>
                        </div>
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'approved' => 'bg-blue-100 text-blue-700',
                                'overdue' => 'bg-red-100 text-red-700',
                                'repaid' => 'bg-slate-100 text-slate-700',
                            ];
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $statusColors[$loan->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $loan->status }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-slate-500">
                        <span>{{ $loan->created_at->format('M d, Y') }}</span>
                        <span>{{ $loan->duration }} Days</span>
                    </div>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">
                    <p>No loan history found.</p>
                </div>
            @endforelse
        </div>
    @else
        <div class="space-y-4">
            @forelse($repayments as $repayment)
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-brand-soft flex items-center justify-center text-brand">
                            <span class="material-symbols-outlined">arrow_downward</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900">₦{{ number_format($repayment->amount, 2) }}</h3>
                            <p class="text-xs text-slate-500">{{ $repayment->paid_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase">{{ $repayment->payment_method }}</span>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400">
                    <p>No repayments found.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>

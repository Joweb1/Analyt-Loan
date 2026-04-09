<div class="min-h-screen bg-slate-50 p-6 pb-32">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Repayment</h1>

    @if($activeLoan)
        <!-- Outstanding Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Outstanding</span>
            @php
                $currency = $activeLoan->amount->getCurrency();
                $repaidMinor = (int) $activeLoan->repayments->sum(fn($r) => $r->amount->getMinorAmount());
                $repaid = new \App\ValueObjects\Money($repaidMinor, $currency);
                $schedules = $activeLoan->scheduledRepayments;
                
                if ($schedules->isNotEmpty()) {
                    $totalDueMinor = (int) $schedules->sum(fn($s) => 
                        $s->principal_amount->getMinorAmount() + 
                        $s->interest_amount->getMinorAmount() + 
                        $s->penalty_amount->getMinorAmount()
                    );
                    $totalDue = new \App\ValueObjects\Money($totalDueMinor, $currency);
                } else {
                    $totalDue = $activeLoan->amount->add($activeLoan->getTotalExpectedInterest());
                }
                
                $balance = $totalDue->subtract($repaid);
                if ($balance->getMinorAmount() < 0) $balance = new \App\ValueObjects\Money(0, $currency);
            @endphp
            <h2 class="text-4xl font-black text-slate-900 mt-2">₦{{ $balance->format() }}</h2>
            @php 
                $nextSchedule = $activeLoan->scheduledRepayments
                    ->whereIn('status', ['pending', 'overdue', 'partial'])
                    ->sortBy('due_date')
                    ->first(); 
            @endphp
            <p class="text-sm text-slate-500 mt-2">
                Next Payment: {{ $nextSchedule ? $nextSchedule->due_date->format('M d, Y') : ($activeLoan->status === 'approved' ? 'Awaiting Disbursement' : 'N/A') }}
            </p>
        </div>

        <!-- Bank Details -->
        <div class="bg-brand rounded-2xl p-6 shadow-lg text-white mb-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <span class="material-symbols-outlined text-9xl">account_balance</span>
            </div>
            <h3 class="font-bold text-lg mb-4">Make a Transfer</h3>
            
            <div class="space-y-4 relative z-10">
                <div>
                    <span class="opacity-70 text-xs uppercase">Bank Name</span>
                    <p class="font-bold text-lg">{{ $activeLoan->organization->repayment_bank_name ?? 'Not Configured' }}</p>
                </div>
                <div>
                    <span class="opacity-70 text-xs uppercase">Account Number</span>
                    <div class="flex items-center gap-2">
                        <p class="font-mono font-bold text-2xl tracking-widest">{{ $activeLoan->organization->repayment_account_number ?? '0000000000' }}</p>
                         <button class="p-1 hover:bg-white/20 rounded" onclick="navigator.clipboard.writeText('{{ $activeLoan->organization->repayment_account_number }}')">
                            <span class="material-symbols-outlined text-sm">content_copy</span>
                        </button>
                    </div>
                </div>
                 <div>
                    <span class="opacity-70 text-xs uppercase">Account Name</span>
                    <p class="font-bold">{{ $activeLoan->organization->repayment_account_name ?? 'Organization Account' }}</p>
                </div>
            </div>
        </div>

        <button wire:click="openUploadModal" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition-colors mb-8">
            I Have Made Payment
        </button>
    @else
        <div class="text-center py-12">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-4xl text-slate-400">
                    {{ $hasPendingApplication ? 'hourglass_empty' : 'check_circle' }}
                </span>
            </div>
            <h3 class="text-lg font-bold text-slate-900">
                {{ $hasPendingApplication ? 'Application Under Review' : 'No Active Loan' }}
            </h3>
            <p class="text-slate-500 max-w-xs mx-auto">
                {{ $hasPendingApplication 
                    ? "Your loan application is being processed. You'll be able to make repayments once it's active." 
                    : "You don't have any outstanding payments at the moment." }}
            </p>
             @if(!$hasPendingApplication)
                <a href="{{ route('borrower.borrow') }}" wire:navigate class="inline-block mt-4 text-brand font-bold">Get a new loan</a>
             @endif
        </div>
    @endif

    <!-- Pending Proofs -->
    @if($pendingProofs->isNotEmpty())
        <h3 class="font-bold text-slate-900 mb-4">Pending Verifications</h3>
        <div class="space-y-3">
            @foreach($pendingProofs as $proof)
                <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex justify-between items-center">
                    <div>
                        <p class="font-bold text-slate-900">₦{{ $proof->amount->format() }}</p>
                        <p class="text-xs text-slate-500">{{ $proof->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded">Pending</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Upload Modal -->
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl animate-in zoom-in-95 duration-300">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-900">Confirm Payment</h3>
                    <button wire:click="$set('showUploadModal', false)" class="p-2 bg-slate-100 rounded-full hover:bg-slate-200">
                        <span class="material-symbols-outlined text-slate-600">close</span>
                    </button>
                </div>

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Amount Paid</label>
                        <input type="number" wire:model="amount" class="w-full rounded-xl border-slate-200 focus:border-brand text-lg">
                        @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Upload Receipt (Optional)</label>
                        <input type="file" wire:model="receipt" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-brand-soft file:text-brand hover:opacity-80">
                         @error('receipt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button wire:click="submitProof" class="w-full bg-brand text-white font-bold py-4 rounded-xl hover:opacity-90">
                    Submit Proof
                </button>
            </div>
        </div>
    @endif
</div>

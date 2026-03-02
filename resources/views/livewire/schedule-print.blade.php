<div class="relative min-h-[1000px] flex flex-col pt-8">
    <!-- Action Button (Floating/Fixed) -->
    <div class="no-print absolute top-0 right-0 flex gap-3">
        <button onclick="window.print()" class="flex items-center gap-2 px-6 py-2 bg-primary text-white rounded-xl font-bold shadow-lg hover:bg-blue-700 transition-all">
            <span class="material-symbols-outlined">print</span>
            Print Schedule
        </button>
        <a href="{{ route('loan.show', $loan->id) }}" class="flex items-center gap-2 px-6 py-2 bg-slate-100 text-slate-600 rounded-xl font-bold hover:bg-slate-200 transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
            Back
        </a>
    </div>

    <!-- Header -->
    <header class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-8">
        <div class="flex items-center gap-4">
            @if($loan->organization->logo_path)
                <img src="{{ $loan->organization->logo_url }}" class="h-16 w-auto object-contain">
            @else
                <div class="size-16 bg-primary rounded-xl flex items-center justify-center text-white">
                    <span class="material-symbols-outlined text-4xl">account_balance</span>
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tighter text-slate-900">{{ $loan->organization->name }}</h1>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mt-1">Repayment Schedule Advice</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">RC Number</p>
            <p class="text-sm font-bold text-slate-900">{{ $loan->organization->rc_number ?? 'N/A' }}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-3">Date Generated</p>
            <p class="text-sm font-bold text-slate-900">{{ now()->format('M d, Y') }}</p>
        </div>
    </header>

    <div class="grid grid-cols-2 gap-12 mb-10">
        <!-- Section 1: Customer Information -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Borrower Information</h2>
            <div class="space-y-2">
                <p class="text-sm font-bold text-slate-900 uppercase">{{ $loan->borrower->user->name }}</p>
                <p class="text-xs text-slate-600">{{ $loan->borrower->phone }}</p>
                <p class="text-xs text-slate-600 font-mono">BVN: {{ $loan->borrower->bvn ?? 'N/A' }}</p>
            </div>
        </section>

        <!-- Section 2: Loan Summary -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Loan Summary</h2>
            <div class="space-y-2">
                <p class="text-sm font-bold text-slate-900 font-mono">{{ $loan->loan_number }}</p>
                <p class="text-xs text-slate-600">Total Principal: ₦{{ number_format($loan->amount, 2) }}</p>
                <p class="text-xs text-slate-600 italic">Cycle: {{ ucfirst($loan->repayment_cycle) }} | Duration: {{ $loan->duration }} {{ $loan->duration_unit }}</p>
            </div>
        </section>
    </div>

    <!-- Section 3: Repayment Schedule -->
    <section class="flex-1">
        <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Scheduled Installments</h2>
        <table class="w-full text-left border-collapse border border-slate-200">
            <thead>
                <tr class="bg-slate-50 text-[10px] font-black uppercase tracking-wider border-b border-slate-200">
                    <th class="px-4 py-3 border-r border-slate-200">Instal. #</th>
                    <th class="px-4 py-3 border-r border-slate-200">Due Date</th>
                    <th class="px-4 py-3 border-r border-slate-200 text-right">Principal</th>
                    <th class="px-4 py-3 border-r border-slate-200 text-right">Interest</th>
                    <th class="px-4 py-3 text-right">Total Due</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @php 
                    $totalP = 0;
                    $totalI = 0;
                @endphp
                @foreach($loan->scheduledRepayments->sortBy('installment_number') as $schedule)
                    @php 
                        $totalP += $schedule->principal_amount;
                        $totalI += $schedule->interest_amount;
                    @endphp
                    <tr class="text-xs">
                        <td class="px-4 py-3 border-r border-slate-200 font-bold text-center">{{ $schedule->installment_number }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 font-medium">{{ $schedule->due_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 text-right font-mono">₦{{ number_format($schedule->principal_amount, 2) }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 text-right font-mono">₦{{ number_format($schedule->interest_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right font-black text-slate-900 font-mono">₦{{ number_format($schedule->principal_amount + $schedule->interest_amount + $schedule->penalty_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-900 text-white font-black">
                    <td colspan="2" class="px-4 py-3 text-right uppercase tracking-widest text-[10px]">Grand Total</td>
                    <td class="px-4 py-3 text-right font-mono text-[11px]">₦{{ number_format($totalP, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-[11px]">₦{{ number_format($totalI, 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm">₦{{ number_format($totalP + $totalI, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </section>

    <!-- Footer Address -->
    <div class="mt-12 text-center text-[9px] text-slate-400 font-bold uppercase tracking-widest border-t border-slate-100 pt-6">
        {{ $loan->organization->address }} | {{ $loan->organization->phone }} | {{ $loan->organization->email }}
    </div>
</div>

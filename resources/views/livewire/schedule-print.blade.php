<div class="bg-white min-h-screen p-8 max-w-4xl mx-auto text-slate-900" id="printable-area">
    <!-- Header -->
    <div class="flex justify-between items-start border-b-4 border-slate-900 pb-6 mb-8">
        <div class="flex items-center gap-6">
            @if($loan->organization->logo_url)
                <img src="{{ $loan->organization->logo_url }}" class="w-20 h-20 object-contain rounded-2xl">
            @else
                <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-300">
                    <span class="material-symbols-outlined text-4xl">domain</span>
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tighter leading-none mb-1">{{ $loan->organization->name }}</h1>
                @if($loan->organization->rc_number)
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">RC: {{ $loan->organization->rc_number }}</p>
                @endif
                <p class="text-xs font-bold text-slate-600 italic">{{ $loan->organization->tagline ?? 'Professional Lending Services' }}</p>
            </div>
        </div>
        <div class="text-right">
            <div class="inline-block px-4 py-2 bg-slate-900 text-white rounded-xl mb-3">
                <p class="text-[10px] font-black uppercase tracking-widest">Repayment Schedule</p>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Generated: {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <div class="flex gap-12 mb-10">
        <!-- Section 1: Borrower Info -->
        <section class="flex-1">
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Borrower Information</h2>
            <div class="space-y-2">
                <p class="text-sm font-black text-slate-900 uppercase tracking-tighter">{{ $loan->borrower->user->name }}</p>
                <p class="text-xs text-slate-500">ID: {{ $loan->borrower->custom_id ?? 'N/A' }}</p>
                <p class="text-xs text-slate-500">Phone: {{ $loan->borrower->phone }}</p>
                <p class="text-xs text-slate-500">{{ $loan->borrower->address }}</p>
            </div>
        </section>

        <!-- Section 2: Loan Summary -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Loan Summary</h2>
            <div class="space-y-2">
                <p class="text-sm font-bold text-slate-900 font-mono">{{ $loan->loan_number }}</p>
                <p class="text-xs text-slate-600">Total Principal: ₦{{ $loan->amount->format() }}</p>
                <p class="text-xs text-slate-600 italic">Cycle: {{ ucfirst($loan->repayment_cycle) }} | Duration: {{ $loan->duration }} {{ $loan->duration_unit }}</p>
            </div>
        </section>
    </div>

    <!-- Main Schedule Table -->
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
                    $currency = $loan->amount->getCurrency();
                    $totalP = new \App\ValueObjects\Money(0, $currency);
                    $totalI = new \App\ValueObjects\Money(0, $currency);
                @endphp
                @foreach($loan->scheduledRepayments->sortBy('installment_number') as $schedule)
                    @php 
                        $totalP = $totalP->add($schedule->principal_amount ?? new \App\ValueObjects\Money(0, $currency));
                        $totalI = $totalI->add($schedule->interest_amount ?? new \App\ValueObjects\Money(0, $currency));
                    @endphp
                    <tr class="text-xs">
                        <td class="px-4 py-3 border-r border-slate-200 font-bold text-center">{{ $schedule->installment_number }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 font-medium">{{ $schedule->due_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 text-right font-mono">₦{{ $schedule->principal_amount?->format() ?? '0.00' }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 text-right font-mono">₦{{ $schedule->interest_amount?->format() ?? '0.00' }}</td>
                        <td class="px-4 py-3 text-right font-black text-slate-900 font-mono">
                            ₦{{ $schedule->principal_amount->add($schedule->interest_amount)->add($schedule->penalty_amount)->format() }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-900 text-white font-black">
                    <td colspan="2" class="px-4 py-3 text-right uppercase tracking-widest text-[10px]">Grand Total</td>
                    <td class="px-4 py-3 text-right font-mono text-[11px]">₦{{ $totalP->format() }}</td>
                    <td class="px-4 py-3 text-right font-mono text-[11px]">₦{{ $totalI->format() }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm">₦{{ $totalP->add($totalI)->format() }}</td>
                </tr>
            </tfoot>
        </table>
    </section>

    <!-- Footer Address -->
    <div class="mt-12 text-center text-[9px] text-slate-400 font-bold uppercase tracking-widest border-t border-slate-100 pt-6">
        {{ $loan->organization->address }} | {{ $loan->organization->phone }} | {{ $loan->organization->email }}
    </div>
</div>

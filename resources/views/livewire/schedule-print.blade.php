<div class="bg-white min-h-screen p-8 max-w-4xl mx-auto text-slate-900" id="printable-area">
    <!-- Header -->
    <div class="flex justify-between items-start border-b-4 border-slate-900 pb-6 mb-8">
        <div class="flex items-center gap-6">
            @if($loan->organization->logo_url)
                <img src="{{ fetch_data($loan?->organization?->logo_url ?? null) }}" class="w-20 h-20 object-contain rounded-2xl">
            @else
                <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-300">
                    <span class="material-symbols-outlined text-4xl">domain</span>
                </div>
            @endif
            <div>
                <h1 class="text-3xl font-black uppercase tracking-tighter leading-none mb-1">{{ fetch_data($loan?->organization?->name ?? null) }}</h1>
                @if($loan->organization->rc_number)
                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">RC: {{ fetch_data($loan?->organization?->rc_number ?? null) }}</p>
                @endif
                <p class="text-xs font-bold text-slate-600 italic">{{ fetch_data($loan?->organization?->tagline ?? 'Professional Lending Services' ?? null) }}</p>
            </div>
        </div>
        <div class="text-right">
            <div class="inline-block px-4 py-2 bg-slate-900 text-white rounded-xl mb-3">
                <p class="text-[10px] font-black uppercase tracking-widest">Repayment Schedule</p>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Generated: {{ fetch_data(now()?->format('M d, Y H:i') ?? null) }}</p>
        </div>
    </div>

    <div class="flex gap-12 mb-10">
        <!-- Section 1: Borrower Info -->
        <section class="flex-1">
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Borrower Information</h2>
            <div class="space-y-2">
                <p class="text-sm font-black text-slate-900 uppercase tracking-tighter">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                <p class="text-xs text-slate-500">ID: {{ fetch_data($loan?->borrower?->custom_id ?? 'N/A' ?? null) }}</p>
                <p class="text-xs text-slate-500">Phone: {{ fetch_data($loan?->borrower?->phone ?? null) }}</p>
                <p class="text-xs text-slate-500">{{ fetch_data($loan?->borrower?->address ?? null) }}</p>
            </div>
        </section>

        <!-- Section 2: Loan Summary -->
        <section>
            <h2 class="text-xs font-black bg-slate-900 text-white px-3 py-1 uppercase tracking-[0.2em] mb-4 inline-block">Loan Summary</h2>
            <div class="space-y-2">
                <p class="text-sm font-bold text-slate-900 font-mono">{{ fetch_data($loan?->loan_number ?? null) }}</p>
                <p class="text-xs text-slate-600">Total Principal: ₦{{ fetch_data($loan?->amount?->format() ?? null) }}</p>
                <p class="text-xs text-slate-600 italic">Cycle: {{ fetch_data(ucfirst($loan?->repayment_cycle) ?? null) }} | Duration: {{ fetch_data($loan?->duration ?? null) }} {{ fetch_data($loan?->duration_unit ?? null) }}</p>
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
                        <td class="px-4 py-3 border-r border-slate-200 font-bold text-center">{{ fetch_data($schedule?->installment_number ?? null) }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 font-medium">{{ fetch_data($schedule?->due_date?->format('M d, Y') ?? null) }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 text-right font-mono">₦{{ fetch_data($schedule?->principal_amount?->format() ?? '0.00' ?? null) }}</td>
                        <td class="px-4 py-3 border-r border-slate-200 text-right font-mono">₦{{ fetch_data($schedule?->interest_amount?->format() ?? '0.00' ?? null) }}</td>
                        <td class="px-4 py-3 text-right font-black text-slate-900 font-mono">
                            ₦{{ fetch_data($schedule?->principal_amount?->add($schedule?->interest_amount)?->add($schedule?->penalty_amount)?->format() ?? null) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-900 text-white font-black">
                    <td colspan="2" class="px-4 py-3 text-right uppercase tracking-widest text-[10px]">Grand Total</td>
                    <td class="px-4 py-3 text-right font-mono text-[11px]">₦{{ fetch_data($totalP?->format() ?? null) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-[11px]">₦{{ fetch_data($totalI?->format() ?? null) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-sm">₦{{ fetch_data($totalP?->add($totalI)?->format() ?? null) }}</td>
                </tr>
            </tfoot>
        </table>
    </section>

    <!-- Footer Address -->
    <div class="mt-12 text-center text-[9px] text-slate-400 font-bold uppercase tracking-widest border-t border-slate-100 pt-6">
        {{ fetch_data($loan?->organization?->address ?? null) }} | {{ fetch_data($loan?->organization?->phone ?? null) }} | {{ fetch_data($loan?->organization?->email ?? null) }}
    </div>
</div>

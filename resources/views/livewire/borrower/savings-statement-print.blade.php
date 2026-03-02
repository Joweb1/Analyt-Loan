<div class="space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-start border-b-2 border-slate-900 pb-8">
        <div class="flex items-center gap-4">
            @if($borrower->organization->logo_path)
                <img src="{{ $borrower->organization->logo_url }}" class="h-16 w-auto" alt="Logo">
            @else
                <div class="size-16 bg-slate-950 rounded flex items-center justify-center text-white font-black text-3xl">A</div>
            @endif
            <div>
                <h1 class="text-2xl font-black uppercase tracking-tight">{{ $borrower->organization->name }}</h1>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ $borrower->organization->address ?? 'Nigeria' }}</p>
                <p class="text-[10px] font-bold text-slate-400 mt-1">{{ $borrower->organization->email ?? '' }} | {{ $borrower->organization->phone ?? '' }}</p>
            </div>
        </div>
        <div class="text-right">
            <h2 class="text-3xl font-black text-slate-900 uppercase">Savings Statement</h2>
            <p class="text-xs font-bold text-slate-500 uppercase mt-1">Generated on {{ now()->format('M d, Y') }}</p>
        </div>
    </div>

    <!-- Customer & Account Info -->
    <div class="grid grid-cols-2 gap-12">
        <div class="space-y-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Customer Details</h3>
            <div>
                <p class="text-lg font-black text-slate-900">{{ $borrower->user->name }}</p>
                <p class="text-sm font-bold text-slate-600">{{ $borrower->phone }}</p>
                <p class="text-sm text-slate-500">{{ $borrower->address }}</p>
            </div>
        </div>
        <div class="space-y-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Account Summary</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Account Number</p>
                    <p class="text-sm font-black text-slate-900">{{ $savingsAccount->account_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Account Status</p>
                    <p class="text-sm font-black text-green-600 uppercase">{{ $savingsAccount->status }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Interest Rate</p>
                    <p class="text-sm font-black text-slate-900">{{ $savingsAccount->interest_rate }}% P.A</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Current Balance</p>
                    <p class="text-sm font-black text-slate-950">₦{{ number_format($savingsAccount->balance, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="mt-8">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Transaction History</h3>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-y-2 border-slate-900">
                    <th class="py-3 px-2 text-[10px] font-black uppercase">Date</th>
                    <th class="py-3 px-2 text-[10px] font-black uppercase">Reference</th>
                    <th class="py-3 px-2 text-[10px] font-black uppercase">Type</th>
                    <th class="py-3 px-2 text-[10px] font-black uppercase">Notes</th>
                    <th class="py-3 px-2 text-right text-[10px] font-black uppercase">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php $runningBalance = $savingsAccount->balance; @endphp
                @foreach($transactions as $t)
                    <tr>
                        <td class="py-4 px-2 text-xs font-bold text-slate-900">{{ $t->transaction_date->format('d/m/Y') }}</td>
                        <td class="py-4 px-2 text-xs font-mono text-slate-500 uppercase">{{ $t->reference }}</td>
                        <td class="py-4 px-2 text-xs font-black uppercase {{ $t->type === 'deposit' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $t->type }}
                        </td>
                        <td class="py-4 px-2 text-xs text-slate-600 italic max-w-xs">{{ $t->notes ?? '-' }}</td>
                        <td class="py-4 px-2 text-right text-xs font-black text-slate-900">
                            {{ $t->type === 'deposit' ? '+' : '-' }}₦{{ number_format($t->amount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-900 bg-slate-50">
                    <td colspan="4" class="py-4 px-2 text-sm font-black uppercase text-right">Closing Balance</td>
                    <td class="py-4 px-2 text-right text-lg font-black text-slate-950">₦{{ number_format($savingsAccount->balance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Footer -->
    <div class="mt-20 pt-8 border-t border-slate-100 flex justify-between items-end">
        <div class="space-y-1">
            <p class="text-[10px] font-black uppercase text-slate-400">Authorized Signature</p>
            <div class="h-12 w-48 border-b border-slate-300"></div>
            <p class="text-[9px] font-bold text-slate-500">Managing Director / Branch Manager</p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-bold text-slate-400">Analyt Loan • Automated Lending System</p>
            <p class="text-[9px] text-slate-300">This is a computer-generated statement and does not require a physical stamp unless requested.</p>
        </div>
    </div>

    <div class="no-print mt-8 flex justify-center">
        <button onclick="window.print()" class="px-8 py-3 bg-slate-950 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-xl flex items-center gap-2 hover:scale-105 transition-all">
            <span class="material-symbols-outlined">print</span>
            Confirm Print
        </button>
    </div>
</div>

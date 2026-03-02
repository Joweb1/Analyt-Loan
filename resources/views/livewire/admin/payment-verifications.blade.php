<div class="max-w-6xl mx-auto w-full">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Payment Verifications</h1>
            <p class="text-slate-500 mt-1">Review and approve payment proofs uploaded by borrowers.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-100 dark:border-zinc-800 overflow-hidden">
        @if($proofs->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-50 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-slate-400 text-3xl">check_circle</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">All Caught Up</h3>
                <p class="text-slate-500 mt-1">There are no pending payment verifications.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-zinc-800 text-xs uppercase text-slate-500 bg-slate-50 dark:bg-zinc-800/50">
                            <th class="px-6 py-4 font-bold">Date</th>
                            <th class="px-6 py-4 font-bold">Borrower</th>
                            <th class="px-6 py-4 font-bold">Amount</th>
                            <th class="px-6 py-4 font-bold">Reference</th>
                            <th class="px-6 py-4 font-bold">Proof</th>
                            <th class="px-6 py-4 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @foreach($proofs as $proof)
                            <tr class="group hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $proof->created_at->format('M d, H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $proof->borrower->user->name }}</span>
                                        <span class="text-xs text-slate-500">Loan #{{ $proof->loan->loan_number }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    ₦{{ number_format($proof->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-mono text-slate-500">
                                    {{ $proof->reference_code }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($proof->receipt_path)
                                        <a href="{{ $proof->receipt_url }}" target="_blank" class="flex items-center gap-2 text-blue-600 hover:underline text-sm font-medium">
                                            <span class="material-symbols-outlined text-lg">attachment</span>
                                            View Receipt
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs italic">No file uploaded</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="reject('{{ $proof->id }}')" wire:confirm="Are you sure you want to reject this payment?" class="p-2 hover:bg-red-50 text-red-600 rounded-lg transition-colors" title="Reject">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                        <button wire:click="approve('{{ $proof->id }}')" wire:confirm="Confirm and record this payment?" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-colors flex items-center gap-2 shadow-lg shadow-slate-900/20">
                                            <span class="material-symbols-outlined text-sm">check</span>
                                            Approve
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
                {{ $proofs->links() }}
            </div>
        @endif
    </div>
</div>

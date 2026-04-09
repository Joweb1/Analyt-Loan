<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold dark:text-white">Welcome back, {{ Auth::user()->name }}</h1>
            <p class="text-gray-500">Track your loans and repayments here.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Active Loan Card -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-widest mb-4">Current Balance</h3>
                @if($activeLoan)
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black dark:text-white">₦{{ $activeLoan->amount->format() }}</span>
                        <span class="text-xs text-green-600 font-bold mb-1">Active</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Next payment due: {{ $activeLoan->scheduledRepayments->where('status', 'pending')->first()?->due_date?->format('M d, Y') ?? 'N/A' }}</p>
                @else
                    <p class="text-gray-400 font-medium">No active loans</p>
                    <a href="#" class="inline-block mt-4 text-primary text-sm font-bold">Apply for a loan</a>
                @endif
            </div>

            <!-- Trust Score -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-widest mb-4">Trust Score</h3>
                <div class="flex items-center gap-4">
                    <div class="text-3xl font-black dark:text-white">{{ Auth::user()->borrower->trust_score ?? 0 }}</div>
                    <div class="flex-1 h-2 bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div style="width: {{ Auth::user()->borrower->trust_score ?? 0 }}%" class="h-full bg-primary"></div>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Maintain timely payments to increase your score.</p>
            </div>
        </div>

        <!-- Loan History -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-zinc-800">
                <h3 class="font-bold dark:text-white">Your Loan History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Loan #</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Applied Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                        @forelse($loans as $loan)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium dark:text-white">{{ $loan->loan_number }}</td>
                                <td class="px-6 py-4 text-sm dark:text-white">₦{{ $loan->amount->format() }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full {{ $loan->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $loan->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $loan->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">You haven't applied for any loans yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

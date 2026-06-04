<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold dark:text-white">Welcome back, {{ fetch_data(Auth::user()?->name ?? null) }}</h1>
            <p class="text-gray-500">Track your loans and repayments here.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Active Loan Card -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-widest mb-4">Current Balance</h3>
                @if($activeLoan)
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-black dark:text-white">₦{{ fetch_data($activeLoan?->amount?->format() ?? null) }}</span>
                        <span class="text-xs text-green-600 font-bold mb-1">Active</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Next payment due: {{ fetch_data($activeLoan?->scheduledRepayments?->where('status', 'pending')?->first()?->due_date?->format('M d, Y') ?? 'N/A' ?? null) }}</p>
                @else
                    <p class="text-gray-400 font-medium">No active loans</p>
                    <a href="#" class="inline-block mt-4 text-primary text-sm font-bold">Apply for a loan</a>
                @endif
            </div>

            <!-- Trust Score -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-800">
                <h3 class="text-xs font-bold uppercase text-gray-500 tracking-widest mb-4">Trust Score</h3>
                <div class="flex items-center gap-4">
                    <div class="text-3xl font-black dark:text-white">{{ fetch_data(Auth::user()?->borrower?->trust_score ?? 0 ?? null) }}</div>
                    <div class="flex-1 h-2 bg-gray-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div style="width: {{ fetch_data(Auth::user()?->borrower?->trust_score ?? 0 ?? null) }}%" class="h-full bg-primary"></div>
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
                                <td class="px-6 py-4 text-sm font-medium dark:text-white">{{ fetch_data($loan?->loan_number ?? null) }}</td>
                                <td class="px-6 py-4 text-sm dark:text-white">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full {{ fetch_data($loan?->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?? null) }}">
                                        {{ fetch_data($loan?->status ?? null) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ fetch_data($loan?->created_at?->format('M d, Y') ?? null) }}</td>
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

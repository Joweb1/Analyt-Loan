<div class="p-2 max-w-7xl mx-auto w-full space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('loan') }}" class="hover:text-primary transition-colors">Loans</a>
            <span>/</span>
            <span class="font-bold text-gray-900 dark:text-white">Pending Approvals</span>
        </div>
        <h2 class="text-3xl font-black tracking-tight dark:text-white">Pending Applications</h2>
        <p class="text-gray-500 text-sm">Review and process incoming loan requests.</p>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Borrower</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tracking Number</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Applied Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($loans as $loan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors cursor-pointer" onclick="window.location='{{ route('loan.show', $loan->id) }}'">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">
                                    {{ substr($loan->borrower->user->name ?? 'U', 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $loan->borrower->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $loan->borrower->phone }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $loan->loan_number }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">₦{{ $loan->amount->format() }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $loan->duration }} {{ $loan->duration_unit }}(s)</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $loan->created_at->format('M d, Y') }}
                            <span class="block text-xs text-gray-400">{{ $loan->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('loan.show', $loan->id) }}" class="bg-primary text-white px-5 py-2 rounded-lg text-xs font-bold hover:bg-primary/90 transition-colors shadow-lg shadow-primary/20 inline-block">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">inbox</span>
                                <p class="text-gray-500 font-medium">No pending applications found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
            {{ $loans->links() }}
        </div>
    </div>
</div>

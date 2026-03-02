<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">KYC Approval</h1>
        <p class="text-sm text-slate-500">Review and approve pending KYC applications.</p>
    </div>

    <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Customer</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">BVN / NIN</th>
                        <th class="px-4 py-3">Registered On</th>
                        <th class="px-4 py-3 text-right rounded-r-xl">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowers as $borrower)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center" style="background-image: url('{{ $borrower->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($borrower->user->name) }}')"></div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $borrower->user->name }}</p>
                                        <p class="text-[10px] text-slate-500">{{ $borrower->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">{{ $borrower->phone }}</td>
                            <td class="px-4 py-4">
                                <p class="text-xs">BVN: <span class="font-mono">{{ $borrower->bvn ?? 'N/A' }}</span></p>
                                <p class="text-xs">NIN: <span class="font-mono">{{ $borrower->national_identity_number ?? 'N/A' }}</span></p>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                {{ $borrower->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button wire:click="approveKyc('{{ $borrower->id }}')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Approve
                                </button>
                                <a href="{{ route('borrower.profile', $borrower->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors ml-2">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">verified</span>
                                No pending KYC applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $borrowers->links() }}
        </div>
    </div>
</div>

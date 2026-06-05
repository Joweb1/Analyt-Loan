<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('dashboard') }}" class="size-10 flex items-center justify-center bg-surface rounded-xl border border-border-main text-slate-500 hover:text-primary transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">KYC Approval</h1>
            <p class="text-sm text-slate-500">Review and approve pending KYC applications.</p>
        </div>
    </div>

    <div class="bg-surface rounded-2xl shadow-sm border border-border-main p-6">
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
                                    <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center" style="background-image: url('{{ fetch_data($borrower?->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($borrower?->user?->name) ?? null) }}')"></div>
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white">{{ fetch_data($borrower?->user?->name ?? null) }}</p>
                                        <p class="text-[10px] text-slate-500">{{ fetch_data($borrower?->user?->email ?? null) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">{{ fetch_data($borrower?->phone ?? null) }}</td>
                            <td class="px-4 py-4">
                                <p class="text-xs">BVN: <span class="font-mono">{{ fetch_data($borrower?->bvn ?? 'N/A' ?? null) }}</span></p>
                                <p class="text-xs">NIN: <span class="font-mono">{{ fetch_data($borrower?->national_identity_number ?? 'N/A' ?? null) }}</span></p>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                {{ fetch_data($borrower?->created_at?->format('M d, Y h:i A') ?? null) }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button wire:click="approveKyc('{{ fetch_data($borrower?->id ?? null) }}')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Approve
                                </button>
                                <a href="{{ fetch_data(route('borrower.profile', $borrower?->id) ?? null) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors ml-2">
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
            {{ fetch_data($borrowers?->links() ?? null) }}
        </div>
    </div>
</div>

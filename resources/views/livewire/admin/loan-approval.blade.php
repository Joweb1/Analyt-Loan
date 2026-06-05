<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('dashboard') }}" class="size-10 flex items-center justify-center bg-surface rounded-xl border border-border-main text-slate-500 hover:text-primary transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Loan Approval</h1>
            <p class="text-sm text-slate-500">Review and approve applied loan applications.</p>
        </div>
    </div>

    <div class="bg-surface rounded-2xl shadow-sm border border-border-main p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Loan ID</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Amount Requested</th>
                        <th class="px-4 py-3">Applied On</th>
                        <th class="px-4 py-3 text-right rounded-r-xl">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-4 font-bold text-slate-900 dark:text-white">
                                <div class="flex flex-col gap-1">
                                    <span>{{ fetch_data($loan?->loan_number ?? null) }}</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-md w-fit font-black uppercase tracking-tighter {{ fetch_data($loan?->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' ?? null) }}">
                                        {{ fetch_data($loan?->status ?? null) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-bold text-slate-900 dark:text-white">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                                <p class="text-[10px] text-slate-500">{{ fetch_data($loan?->borrower?->phone ?? null) }}</p>
                            </td>
                            <td class="px-4 py-4 font-medium text-slate-900 dark:text-white">
                                ₦{{ fetch_data($loan?->amount?->format() ?? null) }}
                                <p class="text-[10px] text-slate-500">{{ fetch_data($loan?->duration ?? null) }} {{ fetch_data($loan?->duration_unit ?? null) }}</p>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                {{ fetch_data($loan?->created_at?->format('M d, Y') ?? null) }}
                            </td>
                            <td class="px-4 py-4 text-right">
                                @if($loan->status === 'applied')
                                    <button wire:click="approveLoan('{{ fetch_data($loan?->id ?? null) }}')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[14px]">fact_check</span> Approve
                                    </button>
                                @elseif($loan->status === 'approved')
                                    <button wire:click="activateLoan('{{ fetch_data($loan?->id ?? null) }}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm inline-flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[14px]">payments</span> Activate
                                    </button>
                                @endif
                                <a href="{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors ml-2">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">task</span>
                                No pending loan applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ fetch_data($loans?->links() ?? null) }}
        </div>
    </div>
</div>

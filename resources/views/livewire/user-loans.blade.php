<div class="w-full mx-auto space-y-8 p-0 relative">
    {{-- Fixed Back Button --}}
    <button onclick="window.history.back()" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-slate-200 dark:border-white/20 rounded-full text-slate-900 dark:text-white hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </button>

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('customer') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">User Loans</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Loan History</h2>
        </div>
        <div class="flex gap-3">
             <a href="{{ fetch_data(route('loan.create', ['borrower_id' => $borrower?->id]) ?? null) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                New Loan Application
            </a>
        </div>
    </div>

    <div class="flex flex-wrap gap-8 px-1 lg:px-2 pb-8 items-start">
        <!-- Left Column: Customer Summary Card (Borrowed from LoanDetails) -->
        <div class="w-full lg:w-[380px] shrink-0 space-y-6">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
                <div class="p-6 relative">
                    <div class="absolute top-4 right-4">
                        <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider">Borrower</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        @php
                            $initials = collect(explode(' ', $borrower?->user?->name ?? ''))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                            $colors = ['bg-blue-50 text-blue-600', 'bg-purple-50 text-purple-600', 'bg-emerald-50 text-emerald-600', 'bg-rose-50 text-rose-600', 'bg-amber-50 text-amber-600'];
                            $colorClass = $colors[ord(substr($borrower?->user?->name ?? ' ', 0, 1)) % count($colors)];
                        @endphp
                        
                        <div class="size-24 rounded-full bg-slate-100 p-1 border-2 border-white dark:border-slate-700 shadow-lg mb-4">
                            @if($borrower->photo_url)
                                <div class="size-full rounded-full bg-cover bg-center" style="background-image: url('{{ fetch_data($borrower?->photo_url ?? null) }}')"></div>
                            @else
                                <div class="size-full rounded-full {{ $colorClass }} flex items-center justify-center">
                                    <span class="font-black text-2xl tracking-tighter">{{ $initials }}</span>
                                </div>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ fetch_data($borrower?->user?->name ?? null) }}</h3>
                        <p class="text-sm text-slate-500 font-medium mb-1 break-all">{{ fetch_data($borrower?->user?->email ?? null) }}</p>
                        <div class="flex items-center gap-1 text-xs text-slate-400">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            {{ fetch_data($borrower?->address ?? 'Lagos, Nigeria' ?? null) }}
                        </div>
                    </div>
                    
                    <div class="mt-8 space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide whitespace-nowrap">Phone</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white text-right break-all">{{ fetch_data($borrower?->phone ?? 'N/A' ?? null) }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide whitespace-nowrap">BVN</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white font-mono text-right break-all">{{ fetch_data($borrower?->bvn ?? 'N/A' ?? null) }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide whitespace-nowrap">Credit Score</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white text-right">{{ fetch_data($borrower?->credit_score ?? '0' ?? null) }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide whitespace-nowrap">Repayment Score</span>
                            <span class="text-sm font-bold text-primary text-right">{{ fetch_data($borrower?->trust_score ?? '0' ?? null) }}%</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 gap-2">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide whitespace-nowrap">Total Loans</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white text-right">{{ fetch_data($borrower?->loans?->count() ?? null) }}</span>
                        </div>
                        <a href="{{ fetch_data(route('borrower.profile', $borrower?->id) ?? null) }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-bold w-full mt-4">
                            <span class="material-symbols-outlined text-sm">account_circle</span> View User Profile
                        </a>
                    </div>

                    <div class="mt-8 grid grid-cols-2 gap-2">
                        @can('communicate_with_customers')
                            <a href="tel:{{ fetch_data($borrower?->phone ?? null) }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">call</span> Call
                            </a>
                            <a href="sms:{{ fetch_data($borrower?->phone ?? null) }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-green-50 text-green-600 hover:bg-green-100 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">sms</span> SMS
                            </a>
                        @endcan
                        @can('send_customer_messages')
                            <button wire:click="$dispatchTo('borrower.message-modal', 'openMessageModal', { borrowerId: '{{ fetch_data($borrower?->id ?? null) }}' })" class="col-span-2 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-primary/20 text-primary hover:bg-primary/5 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">chat_bubble</span> Send Message
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500 uppercase">Profile Status</span>
                    <span class="flex items-center gap-1 text-[10px] font-black uppercase text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-xs">verified</span> Verified
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Loans Grid -->
        <div class="flex-1 min-w-[320px] space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Loan Records</h3>
                <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                    <span class="px-3 py-1 text-[10px] font-black uppercase text-slate-500 tracking-wider">Showing {{ fetch_data($loans?->total() ?? null) }} total</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @forelse($loans as $loan)
                    @php 
                        $risk = $this->getRiskLevel($borrower->trust_score ?? 0);
                        $paidMinor = (int) $loan->repayments->sum(fn($r) => $r->amount->getMinorAmount());
                        $paid = new \App\ValueObjects\Money($paidMinor, $loan->amount->getCurrency());
                        $progress = $loan->amount->isPositive() ? min(100, ($paid->getMajorAmount() / $loan->amount->getMajorAmount()) * 100) : 0;
                        $statusColor = match($loan->status) {
                            'active', 'approved' => 'green',
                            'overdue' => 'red',
                            'repaid' => 'blue',
                            'declined' => 'slate',
                            default => 'yellow'
                        };
                    @endphp
                    <div class="bg-white dark:bg-[#1a1f2b] p-5 rounded-none border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 group cursor-pointer border-l-4 border-l-{{ $statusColor }}-500" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="px-2 py-0.5 rounded-lg bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 text-[9px] font-black uppercase tracking-widest border border-{{ $statusColor }}-200">
                                    {{ fetch_data(str_replace('_', ' ', $loan?->status) ?? null) }}
                                </span>
                                <p class="text-[10px] font-mono font-bold text-slate-400 mt-2">{{ fetch_data($loan?->loan_number ?? null) }}</p>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors">arrow_forward_ios</span>
                        </div>

                        <div class="mb-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Loan Amount</p>
                            <h4 class="text-xl font-black text-slate-900 dark:text-white">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</h4>
                        </div>

                        <div class="space-y-3">
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-white/10 rounded-full">
                                <div class="bg-{{ $statusColor }}-500 h-1.5 rounded-full transition-all" style="width: {{ $progress }}%;"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider">
                                <span class="text-slate-400">{{ round($progress) }}% REPAID</span>
                                <span class="text-{{ $statusColor }}-600">BAL: ₦{{ fetch_data($loan?->amount?->subtract($paid)?->format() ?? null) }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-50 dark:border-slate-800/50 flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Release Date</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ fetch_data($loan?->created_at?->format('M d, Y') ?? null) }}</span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Repayment</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ fetch_data(ucfirst($loan?->repayment_cycle ?? 'Monthly') ?? null) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 flex flex-col items-center justify-center text-slate-400 bg-slate-50 dark:bg-slate-800/20 rounded-3xl border-2 border-dashed border-slate-100 dark:border-slate-800">
                        <span class="material-symbols-outlined text-5xl mb-4 opacity-30">contract</span>
                        <p class="text-lg font-bold">No loan records found</p>
                        <p class="text-sm">This customer hasn't applied for any loans yet.</p>
                        <a href="{{ fetch_data(route('loan.create', ['borrower_id' => $borrower?->id]) ?? null) }}" class="mt-6 px-6 py-2 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:scale-105 transition-all">
                            Create First Loan
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ fetch_data($loans?->links() ?? null) }}
            </div>
        </div>
    </div>

    <livewire:borrower.message-modal :borrower="$borrower" />
</div>

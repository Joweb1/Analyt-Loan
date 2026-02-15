<div class="w-full mx-auto space-y-8 p-0">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('borrowers.index') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">User Loans</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Loan History</h2>
        </div>
        <div class="flex gap-3">
             <a href="{{ route('loan.create', ['borrower_id' => $borrower->id]) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                New Loan Application
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-1 lg:px-2 pb-8">
        <!-- Left Column: Customer Summary Card (Borrowed from LoanDetails) -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
                <div class="p-6 relative">
                    <div class="absolute top-4 right-4">
                        <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider">Borrower</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        @php
                            $initials = collect(explode(' ', $borrower->user->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                            $colors = ['bg-blue-50 text-blue-600', 'bg-purple-50 text-purple-600', 'bg-emerald-50 text-emerald-600', 'bg-rose-50 text-rose-600', 'bg-amber-50 text-amber-600'];
                            $colorClass = $colors[ord(substr($borrower->user->name, 0, 1)) % count($colors)];
                        @endphp
                        
                        <div class="size-24 rounded-full bg-slate-100 p-1 border-2 border-white dark:border-slate-700 shadow-lg mb-4">
                            @if($borrower->photo_url)
                                <div class="size-full rounded-full bg-cover bg-center" style="background-image: url('{{ $borrower->photo_url }}')"></div>
                            @else
                                <div class="size-full rounded-full {{ $colorClass }} flex items-center justify-center">
                                    <span class="font-black text-2xl tracking-tighter">{{ $initials }}</span>
                                </div>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $borrower->user->name }}</h3>
                        <p class="text-sm text-slate-500 font-medium mb-1">{{ $borrower->user->email }}</p>
                        <div class="flex items-center gap-1 text-xs text-slate-400">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            {{ $borrower->address ?? 'Lagos, Nigeria' }}
                        </div>
                    </div>
                    
                    <div class="mt-8 space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Phone</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $borrower->phone ?? 'N/A' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">BVN</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $borrower->bvn ?? 'N/A' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Credit Score</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $borrower->credit_score ?? '0' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Total Loans</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $borrower->loans->count() }}</span>
                        </div>
                        <a href="{{ route('borrower.profile', $borrower->id) }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-bold w-full mt-4">
                            <span class="material-symbols-outlined text-sm">account_circle</span> View User Profile
                        </a>
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
        <div class="md:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Loan Records</h3>
                <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                    <span class="px-3 py-1 text-[10px] font-black uppercase text-slate-500 tracking-wider">Showing {{ $loans->total() }} total</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @forelse($loans as $loan)
                    @php 
                        $risk = $this->getRiskLevel($borrower->credit_score ?? 0);
                        $paid = $loan->repayments->sum('amount');
                        $progress = $loan->amount > 0 ? min(100, ($paid / $loan->amount) * 100) : 0;
                        $statusColor = match($loan->status) {
                            'active', 'approved' => 'green',
                            'overdue' => 'red',
                            'repaid' => 'blue',
                            'declined' => 'slate',
                            default => 'yellow'
                        };
                    @endphp
                    <div class="bg-white dark:bg-[#1a1f2b] p-5 rounded-none border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 group cursor-pointer border-l-4 border-l-{{ $statusColor }}-500" onclick="window.location='{{ route('loan.show', $loan->id) }}'">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="px-2 py-0.5 rounded-lg bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 text-[9px] font-black uppercase tracking-widest border border-{{ $statusColor }}-200">
                                    {{ str_replace('_', ' ', $loan->status) }}
                                </span>
                                <p class="text-[10px] font-mono font-bold text-slate-400 mt-2">{{ $loan->loan_number }}</p>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 group-hover:text-primary transition-colors">arrow_forward_ios</span>
                        </div>

                        <div class="mb-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Loan Amount</p>
                            <h4 class="text-xl font-black text-slate-900 dark:text-white">₦{{ number_format($loan->amount, 2) }}</h4>
                        </div>

                        <div class="space-y-3">
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-white/10 rounded-full">
                                <div class="bg-{{ $statusColor }}-500 h-1.5 rounded-full transition-all" style="width: {{ $progress }}%;"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] font-black uppercase tracking-wider">
                                <span class="text-slate-400">{{ round($progress) }}% REPAID</span>
                                <span class="text-{{ $statusColor }}-600">BAL: ₦{{ number_format(max(0, $loan->amount - $paid), 2) }}</span>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-50 dark:border-slate-800/50 flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Release Date</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $loan->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex flex-col text-right">
                                <span class="text-[9px] font-black text-slate-400 uppercase">Repayment</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ ucfirst($loan->repayment_cycle ?? 'Monthly') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 flex flex-col items-center justify-center text-slate-400 bg-slate-50 dark:bg-slate-800/20 rounded-3xl border-2 border-dashed border-slate-100 dark:border-slate-800">
                        <span class="material-symbols-outlined text-5xl mb-4 opacity-30">contract</span>
                        <p class="text-lg font-bold">No loan records found</p>
                        <p class="text-sm">This customer hasn't applied for any loans yet.</p>
                        <a href="{{ route('loan.create', ['borrower_id' => $borrower->id]) }}" class="mt-6 px-6 py-2 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:scale-105 transition-all">
                            Create First Loan
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $loans->links() }}
            </div>
        </div>
    </div>
</div>

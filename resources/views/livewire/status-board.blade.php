<div class="w-full mx-auto px-2 pt-6 pb-2 shrink-0 h-full flex flex-col" x-data="{ view: 'board' }">
    {{-- Fixed Back Button --}}
    <button onclick="window.history.back()" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-slate-200 dark:border-white/20 rounded-full text-slate-900 dark:text-white hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </button>

    <div class="flex sm:flex-row sm:items-center gap-2 text-xs font-semibold text-[#606e8a] uppercase tracking-wider mb-2 whitespace-nowrap">
        <a class="hover:text-primary transition-colors" href="{{ route('loan') }}">Loan Management</a><span>/</span><span class="text-[#111318] dark:text-white">Status Board</span>
    </div>
    
    <!-- Header & Controls -->
    <div class="flex flex-col gap-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#111318] dark:text-white tracking-tight">Status Board</h2>
                <p class="text-[#606e8a] text-sm mt-1">Total Active Pipeline: ₦{{ fetch_data($totalPipelineValue?->format() ?? '0.00' ?? null) }}</p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex p-1 bg-white dark:bg-white/5 rounded-2xl border border-[#dbdee6]">
                    <button @click="view = 'board'" :class="view === 'board' ? 'bg-primary text-white' : 'text-[#606e8a] hover:bg-background-light'" class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all">Board</button>
                    <button @click="view = 'list'" :class="view === 'list' ? 'bg-primary text-white' : 'text-[#606e8a] hover:bg-background-light'" class="px-3 py-1.5 text-xs font-bold rounded-xl transition-all">List View</button>
                </div>
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="flex flex-row items-center gap-3 overflow-x-auto pb-2 custom-scrollbar whitespace-nowrap">
            <x-portfolio-filter :portfolios="$portfolios" :portfolioId="$portfolioId" />
            
            <div class="relative flex-1 min-w-[300px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 text-sm">search</span>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search ID, Name, Phone, BVN, NIN..." class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold focus:ring-2 focus:ring-primary/20 transition-all">
            </div>
            
            <select wire:model.live="statusFilter" class="bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold py-2.5 px-4 focus:ring-2 focus:ring-primary/20 min-w-max">
                <option value="">All Statuses</option>
                <option value="applied">Applied</option>
                <option value="verification_pending">Verification</option>
                <option value="approved">Approved</option>
                <option value="active">Active</option>
                <option value="repaid">Repaid</option>
                <option value="overdue">Overdue</option>
                <option value="declined">Declined</option>
            </select>

            <select wire:model.live="riskFilter" class="bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold py-2.5 px-4 focus:ring-2 focus:ring-primary/20 min-w-max">
                <option value="">All Risks</option>
                <option value="low">Low Risk</option>
                <option value="medium">Medium Risk</option>
                <option value="high">High Risk</option>
            </select>

            <select wire:model.live="dateFilter" class="bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold py-2.5 px-4 focus:ring-2 focus:ring-primary/20 min-w-max">
                <option value="">Any Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
    </div>

    <!-- Board View -->
    <div x-show="view === 'board'" id="board-view" class="flex-1 overflow-x-auto p-2 sm:p-2 pt-4 custom-scrollbar">
        <div class="flex gap-6 h-full min-w-max">
            {{-- Pending --}}
            <div class="kanban-column flex flex-col gap-4 w-80">
                <div class="flex items-center justify-between border-b-2 border-yellow-400 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide uppercase">Pending</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">{{ $counts['pending'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">₦{{ fetch_data($sums['pending']?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 custom-scrollbar h-full">
                    @foreach($pending as $loan)
                        @php $risk = $this->getRiskLevel($loan->borrower->trust_score ?? 0); @endphp
                        <div class="bg-white dark:bg-[#1c2433] p-4 shadow-sm border border-[#dbdee6] dark:border-white/5 hover:shadow-md transition-all group cursor-pointer" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-{{ $risk['color'] }}-100 text-{{ $risk['color'] }}-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">{{ $risk['label'] }}</span>
                                <span class="material-symbols-outlined text-gray-300 group-hover:text-primary text-lg transition-colors">more_horiz</span>
                            </div>
                            <p class="text-sm font-extrabold dark:text-white mb-1">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                            <p class="text-lg font-black text-primary dark:text-slate-200 mb-3">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="size-6 rounded-full border-2 border-white bg-cover bg-center" style="background-image: url('{{ fetch_data($loan?->borrower?->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($loan?->borrower?->user?->name) ?? null) }}')"></div>
                                <p class="text-[10px] text-[#606e8a] font-medium">{{ fetch_data($loan?->updated_at?->diffForHumans(null, true, true) ?? null) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Active --}}
            <div class="kanban-column flex flex-col gap-4 w-80">
                <div class="flex items-center justify-between border-b-2 border-green-400 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide uppercase">Active</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">{{ $counts['active'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">₦{{ fetch_data($sums['active']?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 h-full custom-scrollbar">
                    @foreach($active as $loan)
                        @php 
                            $paidMinor = (int) $loan->repayments->sum(fn($r) => $r->amount->getMinorAmount());
                            $paid = new \App\ValueObjects\Money($paidMinor, $loan->amount->getCurrency());
                            $progress = $loan->amount->isPositive() ? min(100, ($paid->getMajorAmount() / $loan->amount->getMajorAmount()) * 100) : 0;
                        @endphp
                        <div class="bg-white dark:bg-[#1c2433] p-4 shadow-sm border border-[#dbdee6] hover:border-primary/50 transition-all group cursor-pointer border-l-4 border-l-green-500" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                            <div class="flex justify-between items-start mb-2">
                                <p class="text-sm font-extrabold dark:text-white">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                                <span class="material-symbols-outlined text-gray-300 group-hover:text-primary text-lg">open_in_new</span>
                            </div>
                            <p class="text-lg font-black text-primary mb-3">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</p>
                            <div class="w-full h-1 bg-slate-100 dark:bg-white/10 rounded-full mb-1">
                                <div class="bg-green-500 h-1 rounded-full transition-all" style="width: {{ $progress }}%;"></div>
                            </div>
                            <div class="flex justify-between text-[9px] font-black uppercase text-slate-400">
                                <span>{{ round($progress) }}% PAID</span>
                                <span>₦{{ fetch_data($loan?->amount?->subtract($paid)?->format() ?? null) }} REM.</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Overdue --}}
            <div class="kanban-column flex flex-col gap-4 w-80">
                <div class="flex items-center justify-between border-b-2 border-red-500 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide uppercase text-red-500">Overdue</span>
                        <span class="bg-red-100 text-red-700 text-[10px] font-black px-2 py-0.5 rounded-full">{{ $counts['overdue'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-red-400">₦{{ fetch_data($sums['overdue']?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 h-full custom-scrollbar">
                    @foreach($overdue as $loan)
                        @php 
                            $paidMinor = (int) $loan->repayments->sum(fn($r) => $r->amount->getMinorAmount());
                            $paid = new \App\ValueObjects\Money($paidMinor, $loan->amount->getCurrency());
                        @endphp
                        <div class="bg-white dark:bg-[#1c2433] p-4 shadow-sm border border-red-200 dark:border-red-900/30 hover:shadow-md transition-all group cursor-pointer border-l-4 border-l-red-500" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">OVERDUE</span>
                                <span class="material-symbols-outlined text-gray-300 group-hover:text-red-500 text-lg transition-colors">warning</span>
                            </div>
                            <p class="text-sm font-extrabold dark:text-white mb-1">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                            <p class="text-lg font-black text-red-600 mb-3">₦{{ fetch_data($loan?->amount?->subtract($paid)?->format() ?? null) }}</p>
                            <div class="flex items-center justify-between">
                                <div class="size-6 rounded-full border-2 border-white bg-cover bg-center" style="background-image: url('{{ fetch_data($loan?->borrower?->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($loan?->borrower?->user?->name) ?? null) }}')"></div>
                                <p class="text-[10px] text-red-400 font-bold uppercase">Action Required</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Declined --}}
            <div class="kanban-column flex flex-col gap-4 w-80">
                <div class="flex items-center justify-between border-b-2 border-slate-300 pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide uppercase text-slate-500">Declined</span>
                        <span class="bg-slate-200 text-slate-700 text-[10px] font-black px-2 py-0.5 rounded-full">{{ $counts['declined'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-slate-400">₦{{ fetch_data($sums['declined']?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 h-full custom-scrollbar opacity-75">
                    @foreach($declined as $loan)
                        <div class="bg-white dark:bg-[#1c2433] p-4 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-md transition-all group cursor-pointer border-l-4 border-l-slate-400" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase">DECLINED</span>
                                <span class="material-symbols-outlined text-gray-300 group-hover:text-red-500 text-lg transition-colors">block</span>
                            </div>
                            <p class="text-sm font-extrabold dark:text-white mb-1">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                            <p class="text-lg font-black text-slate-400 mb-3 line-through">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</p>
                            <div class="flex items-center justify-between">
                                <div class="size-6 rounded-full border-2 border-white bg-cover bg-center grayscale" style="background-image: url('{{ fetch_data($loan?->borrower?->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($loan?->borrower?->user?->name) ?? null) }}')"></div>
                                <p class="text-[10px] text-slate-400 font-bold uppercase">Application Rejected</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Repaid --}}
            <div class="kanban-column flex flex-col gap-4 w-80">
                <div class="flex items-center justify-between border-b-2 border-[#dbdee6] pb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm tracking-wide uppercase text-[#606e8a]">Repaid</span>
                        <span class="bg-[#dbdee6] text-[#111318] text-[10px] font-black px-2 py-0.5 rounded-full">{{ $counts['repaid'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-[#606e8a]">₦{{ fetch_data($sums['repaid']?->format() ?? null) }}</span>
                </div>
                <div class="flex flex-col gap-4 overflow-y-auto pb-8 h-full custom-scrollbar opacity-60">
                    @foreach($repaid as $loan)
                        <div class="bg-white dark:bg-[#1c2433] p-4 shadow-sm border border-[#dbdee6] hover:border-slate-400 transition-all grayscale cursor-pointer" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                            <p class="text-sm font-extrabold dark:text-white mb-1">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</p>
                            <p class="text-lg font-black text-slate-500">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</p>
                            <div class="flex items-center gap-1 text-[9px] font-black text-green-600 mt-2">
                                <span class="material-symbols-outlined text-xs">verified</span> FULLY REPAID
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div x-show="view === 'list'" id="list-view" class="flex-1 flex flex-col overflow-hidden" style="display: none;">
        <div class="overflow-x-auto bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-[10px] font-black uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4">Loan ID</th>
                        <th class="px-6 py-4">Borrower Details</th>
                        <th class="px-6 py-4">Identification</th>
                        <th class="px-6 py-4">Product / Amount</th>
                        <th class="px-6 py-4">Balance / Paid</th>
                        <th class="px-6 py-4 text-center">Risk / Status</th>
                        <th class="px-6 py-4">Created Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($allLoans as $loan)
                        @php
                            $risk = $this->getRiskLevel($loan->borrower->trust_score ?? 0);
                            $paidMinor = (int) $loan->repayments->sum(fn($r) => $r->amount->getMinorAmount());
                            $paid = new \App\ValueObjects\Money($paidMinor, $loan->amount->getCurrency());
                            $balance = $loan->balance;
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group" onclick="window.location='{{ fetch_data(route('loan.show', $loan?->id) ?? null) }}'">
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono font-bold text-slate-500 group-hover:text-primary transition-colors">{{ fetch_data($loan?->loan_number ?? null) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</span>
                                    <span class="text-[10px] text-slate-500 font-medium">{{ fetch_data($loan?->borrower?->phone ?? null) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[9px] font-black text-slate-400 uppercase">BVN: <span class="text-slate-700 dark:text-slate-300 font-mono">{{ fetch_data($loan?->borrower?->bvn ?? 'N/A' ?? null) }}</span></span>
                                    <span class="text-[9px] font-black text-slate-400 uppercase">NIN: <span class="text-slate-700 dark:text-slate-300 font-mono">{{ fetch_data($loan?->borrower?->national_identity_number ?? 'N/A' ?? null) }}</span></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">{{ fetch_data($loan?->loan_product ?? 'Personal Loan' ?? null) }}</span>
                                    <span class="text-sm font-black text-slate-900 dark:text-white">₦{{ fetch_data($loan?->amount?->format() ?? null) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-primary">BAL: ₦{{ fetch_data($balance?->format() ?? null) }}</span>
                                    <span class="text-[10px] font-black text-green-600">PAID: ₦{{ fetch_data($paid?->format() ?? null) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-{{ $risk['color'] }}-100 text-{{ $risk['color'] }}-700">{{ $risk['label'] }}</span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-600">{{ fetch_data(str_replace('_', ' ', $loan?->status) ?? null) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] font-bold text-slate-500 uppercase">{{ fetch_data($loan?->created_at?->format('M d, Y') ?? null) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 pb-12">
            {{ fetch_data($allLoans?->links() ?? null) }}
        </div>
    </div>
</div>

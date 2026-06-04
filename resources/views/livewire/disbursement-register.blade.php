<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    {{-- Fixed Back Button --}}
    <a href="{{ route('records') }}" class="fixed top-24 right-4 z-40 pl-3 pr-5 py-2 bg-white/30 backdrop-blur-md border border-white/20 rounded-full text-slate-900 hover:bg-white/50 transition-all duration-200 shadow-xl group flex items-center gap-2">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span class="text-[10px] font-black uppercase tracking-widest">Go Back</span>
    </a>

    {{-- Header Section --}}
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Disbursement Register</h1>
                    <p class="hidden sm:block mt-1 text-sm text-gray-500">A premium digital ledger for tracking loan disbursements and repayments.</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <div class="relative">
                    <input type="text" wire:model.live="search" placeholder="Search borrower or loan #..." 
                        class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 ease-in-out">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>
                <select wire:model.live="loan_type" class="block w-full pl-3 pr-10 py-2 text-base border-gray-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                    <option value="">All Types</option>
                    @foreach($loanProducts as $product)
                        <option value="{{ $product }}">{{ $product }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Stats Summary Area --}}
    <div class="max-w-7xl mx-auto grid grid-cols-2 gap-3 lg:grid-cols-4 mb-10">
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-gray-400 truncate uppercase tracking-widest">Total Issued</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-gray-900">{{ $stats['total_issued'] }}</dd>
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-gray-400 truncate uppercase tracking-widest">Active Loans</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-blue-600">{{ $stats['active_count'] }}</dd>
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-gray-400 truncate uppercase tracking-widest">Completed</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-emerald-600">{{ $stats['completed_count'] }}</dd>
        </div>
        <div class="bg-white overflow-hidden shadow-sm border border-gray-200 rounded p-3 sm:p-4 transition hover:shadow-md">
            <dt class="text-[10px] sm:text-xs font-bold text-gray-400 truncate uppercase tracking-widest">Expected Val</dt>
            <dd class="mt-1 text-lg sm:text-2xl font-black text-gray-900">{{ $stats['total_repayment_value'] }}</dd>
        </div>
    </div>

    {{-- Ledger Sections --}}
    <div class="max-w-7xl mx-auto space-y-12">
        @forelse($groupedLoans as $month => $loans)
            <div x-data="{ open: true }">
                <div class="flex items-center justify-between mb-6 cursor-pointer" @click="open = !open">
                    <h2 class="text-xl font-semibold text-gray-400 tracking-wide uppercase flex items-center">
                        <span class="mr-3">{{ $month }}</span>
                        <span class="h-px w-24 bg-gray-200"></span>
                    </h2>
                    <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-1 rounded-md">{{ fetch_data($loans?->count() ?? null) }} Records</span>
                </div>

                <div x-show="open" x-collapse class="space-y-4">
                    @foreach($loans as $index => $loan)
                        <div x-data="{ expanded: false }" 
                            class="bg-white border border-gray-200 rounded shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                            
                            {{-- Card Header --}}
                            <div @click="expanded = !expanded" class="p-5 cursor-pointer flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-50 text-gray-400 text-xs font-medium border border-gray-100">
                                            {{ fetch_data(str_pad($loop?->iteration, 2, '0', STR_PAD_LEFT) ?? null) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h3 class="text-xl font-bold text-gray-900">{{ fetch_data($loan?->borrower?->user?->name ?? null) }}</h3>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono bg-gray-100 text-gray-500 uppercase">{{ fetch_data($loan?->borrower?->custom_id ?? null) }}</span>
                                        </div>
                                        <div class="flex items-center mt-1 space-x-3">
                                            <span class="flex items-center text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                                                Savings: {{ fetch_data($loan?->borrower?->savingsAccount?->balance?->format() ?? 'N/A' ?? null) }}
                                            </span>
                                            <span class="text-xs text-gray-400 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ fetch_data($loan?->loanOfficer?->name ?? null) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between lg:justify-end gap-6">
                                    <div class="text-right">
                                        <span class="block text-[10px] uppercase font-bold text-gray-400 tracking-widest mb-1">{{ fetch_data($loan?->loan_product ?? null) }}</span>
                                        <span class="text-2xl font-black text-green-600">{{ fetch_data($loan?->amount?->format() ?? null) }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @php
                                            $statusClasses = [
                                                'active' => 'bg-blue-50 text-blue-700 border-blue-100',
                                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                                'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                                'overdue' => 'bg-rose-50 text-rose-700 border-rose-100',
                                            ];
                                            $currentClass = $statusClasses[$loan->status] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $currentClass }}">
                                            {{ fetch_data($loan?->status ?? null) }}
                                        </span>
                                        <svg class="w-5 h-5 text-gray-300 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Body --}}
                            <div x-show="expanded" x-collapse class="border-t border-gray-100 bg-gray-50/30">
                                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-8">
                                    {{-- Details --}}
                                    <div class="space-y-4">
                                        <div>
                                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Loan Schedule</span>
                                            <div class="flex items-center space-x-4">
                                                <div class="bg-white p-2 rounded shadow-sm border border-gray-200 text-center min-w-[70px]">
                                                    <span class="block text-[10px] text-gray-400 uppercase">Released</span>
                                                    <span class="block text-sm font-bold text-gray-700">{{ fetch_data($loan?->release_date?->format('d M') ?? null) }}</span>
                                                </div>
                                                <div class="bg-white p-2 rounded shadow-sm border border-blue-200 text-center min-w-[70px]">
                                                    <span class="block text-[10px] text-blue-400 uppercase">Installment</span>
                                                    <input type="date" 
                                                        value="{{ fetch_data($loan?->installment_date?->format('Y-m-d') ?? null) }}"
                                                        wire:change="updateInstallmentDate('{{ fetch_data($loan?->id ?? null) }}', $event.target.value)"
                                                        class="block w-full text-xs font-bold text-blue-700 bg-transparent border-none focus:ring-0 p-0 text-center">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total With Cost</span>
                                            <span class="text-lg font-bold text-gray-700">{{ fetch_data($loan?->getTotalCost()?->format() ?? null) }}</span>
                                            <span class="text-xs text-gray-400 block mt-1">Includes interest, fees & administrative costs.</span>
                                        </div>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="md:col-span-2">
                                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Register Journal / Notes</span>
                                        <div class="relative">
                                            <textarea 
                                                wire:change="updateNote('{{ fetch_data($loan?->id ?? null) }}', $event.target.value)"
                                                placeholder="Enter private observations or recording notes here..."
                                                class="block w-full rounded border-gray-200 bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm min-h-[100px] resize-none p-4"
                                            >{{ fetch_data($loan?->register_notes ?? null) }}</textarea>
                                            <div class="absolute bottom-3 right-3 opacity-20">
                                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path></svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-100/50 px-6 py-3 flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <button class="text-xs font-semibold text-gray-500 hover:text-blue-600 flex items-center transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Print Slip
                                        </button>
                                        <button class="text-xs font-semibold text-gray-500 hover:text-blue-600 flex items-center transition">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View Full Record
                                        </button>
                                    </div>
                                    <span class="text-[10px] text-gray-400 italic">Last modified: {{ fetch_data($loan?->updated_at?->diffForHumans() ?? null) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No records found</h3>
                <p class="mt-1 text-sm text-gray-500">Adjust your search or filters to find what you're looking for.</p>
            </div>
        @endforelse
    </div>

    {{-- Notification Toast (Minimal) --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-5 right-5 z-50">
        <div class="bg-white border shadow-xl rounded-2xl p-4 flex items-center space-x-3" 
            :class="type === 'success' ? 'border-emerald-100' : 'border-rose-100'">
            <div :class="type === 'success' ? 'text-emerald-500' : 'text-rose-500'">
                <template x-if="type === 'success'">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </div>
            <p class="text-sm font-bold text-gray-800" x-text="message"></p>
        </div>
    </div>
</div>

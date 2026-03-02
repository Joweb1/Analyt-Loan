<div class="w-full mx-auto space-y-8 p-0">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #schedule-modal, #schedule-modal *, #fees-modal, #fees-modal *, #comments-modal, #comments-modal *, #collateral-modal, #collateral-modal *, #delete-modal, #delete-modal * {
                visibility: visible;
            }
            #schedule-modal, #fees-modal, #comments-modal, #collateral-modal, #delete-modal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: white;
                padding: 0;
            }
            /* Hide print/close buttons */
            #schedule-modal button, #fees-modal button, #comments-modal button, #collateral-modal button, #delete-modal button {
                display: none !important;
            }
            /* Hide Actions column */
            #schedule-modal th:last-child, #schedule-modal td:last-child {
                display: none !important;
            }
            /* Expand table container */
            #schedule-modal .overflow-y-auto, #fees-modal .overflow-y-auto, #comments-modal .overflow-y-auto, #collateral-modal .overflow-y-auto {
                overflow: visible !important;
                height: auto !important;
            }
        }
    </style>
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('loan') }}" class="hover:text-primary transition-colors">Loans</a>
                <span>/</span>
                <a href="{{ route('status-board') }}" class="hover:text-primary transition-colors">Status Board</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Loan Details</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Loan Management</h2>
        </div>
        <div class="flex gap-3">
            @can('export_and_print')
                <a href="{{ route('loan.print', $loan->id) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <span class="material-symbols-outlined text-lg">print</span>
                    Print
                </a>
            @endcan
            <a href="{{ route('loan.edit', $loan->id) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">
                <span class="material-symbols-outlined text-lg">edit</span>
                Edit Loan
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-1 lg:px-2 pb-8">
        <!-- Left Column: Customer Card -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
                <div class="p-6 relative">
                    <div class="absolute top-4 right-4">
                        <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider">Borrower</span>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        @php
                            $initials = collect(explode(' ', $loan->borrower->user->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                            $colors = ['bg-blue-50 text-blue-600', 'bg-purple-50 text-purple-600', 'bg-emerald-50 text-emerald-600', 'bg-rose-50 text-rose-600', 'bg-amber-50 text-amber-600'];
                            $colorClass = $colors[ord(substr($loan->borrower->user->name, 0, 1)) % count($colors)];
                        @endphp
                        
                        <div class="size-24 rounded-full bg-slate-100 p-1 border-2 border-white dark:border-slate-700 shadow-lg mb-4">
                            @if($loan->borrower->photo_url)
                                <div class="size-full rounded-full bg-cover bg-center" style="background-image: url('{{ $loan->borrower->photo_url }}')"></div>
                            @else
                                <div class="size-full rounded-full {{ $colorClass }} flex items-center justify-center">
                                    <span class="font-black text-2xl tracking-tighter">{{ $initials }}</span>
                                </div>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $loan->borrower->user->name }}</h3>
                        <p class="text-sm text-slate-500 font-medium mb-1">{{ $loan->borrower->user->email }}</p>
                        <div class="flex items-center gap-1 text-xs text-slate-400">
                            <span class="material-symbols-outlined text-sm">location_on</span>
                            {{ $loan->borrower->address ?? 'Lagos, Nigeria' }}
                        </div>
                    </div>
                    
                    <div class="mt-8 space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Phone</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $loan->borrower->phone ?? 'N/A' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">BVN</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $loan->borrower->bvn ?? 'N/A' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">NIN</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $loan->borrower->national_identity_number ?? 'N/A' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Gender / Age</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ ucfirst($loan->borrower->gender ?? '-') }} / 
                                {{ $loan->borrower->date_of_birth ? \Carbon\Carbon::parse($loan->borrower->date_of_birth)->age : '-' }}
                            </span>
                        </div>
                        <a href="{{ route('borrower.profile', $loan->borrower->id) }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-bold w-full mt-4">
                            <span class="material-symbols-outlined text-sm">account_circle</span> View User Profile
                        </a>
                    </div>
                    
                    <div class="mt-8 grid grid-cols-2 gap-2">
                        @can('communicate_with_customers')
                            <a href="tel:{{ $loan->borrower->phone }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">call</span> Call
                            </a>
                            <a href="sms:{{ $loan->borrower->phone }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-green-50 text-green-600 hover:bg-green-100 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">sms</span> SMS
                            </a>
                        @endcan
                        @can('send_customer_messages')
                            <button wire:click="$dispatchTo('borrower.message-modal', 'openMessageModal', { borrowerId: '{{ $loan->borrower->id }}' })" class="col-span-2 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-primary/20 text-primary hover:bg-primary/5 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">chat_bubble</span> Send Message
                            </button>
                        @endcan
                        <a href="{{ route('borrower.loans', $loan->borrower->id) }}" class="col-span-2 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors text-xs font-bold">
                            <span class="material-symbols-outlined text-sm">visibility</span> View All Loans
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

        <!-- Right Column: Loan Details & Actions -->
        <div class="md:col-span-2 space-y-8">
            <!-- Loan Card -->
             <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden" x-data="{ view: 'card' }">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Loan Information</h3>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $loan->loan_number }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg bg-{{ in_array($loan->status, ['overdue', 'applied', 'verification_pending', 'declined']) ? 'red' : 'green' }}-100 text-{{ in_array($loan->status, ['overdue', 'applied', 'verification_pending', 'declined']) ? 'red' : 'green' }}-700 text-[9px] font-black uppercase tracking-widest border border-{{ in_array($loan->status, ['overdue', 'applied', 'verification_pending', 'declined']) ? 'red' : 'green' }}-200">
                            {{ str_replace('_', ' ', $loan->status) }}
                        </span>

                        @if(in_array($loan->status, ['applied', 'verification_pending']))
                            <div class="flex items-center gap-2">
                                <button wire:click="approveLoan" wire:confirm="Are you sure you want to approve this loan?" class="flex items-center gap-1.5 px-3 py-1.5 bg-green-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-green-700 transition-all shadow-sm">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Approve
                                </button>
                                <button wire:click="declineLoan" wire:confirm="Are you sure you want to decline this loan?" class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-red-100 transition-all shadow-sm">
                                    <span class="material-symbols-outlined text-sm">block</span>
                                    Decline
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="flex bg-slate-100 dark:bg-slate-800 rounded-lg p-1">
                        <button @click="view = 'card'" :class="{ 'bg-white shadow-sm text-primary': view === 'card', 'text-slate-500': view !== 'card' }" class="p-1.5 rounded-md transition-all">
                            <span class="material-symbols-outlined text-lg block">grid_view</span>
                        </button>
                        <button @click="view = 'list'" :class="{ 'bg-white shadow-sm text-primary': view === 'list', 'text-slate-500': view !== 'list' }" class="p-1.5 rounded-md transition-all">
                            <span class="material-symbols-outlined text-lg block">list</span>
                        </button>
                    </div>
                </div>

                <!-- Grid View -->
                <div x-show="view === 'card'" class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-y-8 gap-x-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Loan Amount</p>
                        <p class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">₦{{ number_format($loan->amount, 2) }}</p>
                    </div>
                     <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Current Balance</p>
                        @php
                            $paid = $loan->repayments->sum('amount');
                            $balance = max(0, $loan->amount - $paid); // Simplified logic
                        @endphp
                        <p class="text-lg sm:text-xl font-black text-primary">₦{{ number_format($balance, 2) }}</p>
                    </div>

                    <div class="col-span-2 sm:col-span-3 h-px bg-slate-100 dark:bg-slate-800 my-2"></div>

                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Release Date</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $loan->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Maturity Date</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $loan->created_at->addMonths($loan->duration ?? 1)->format('M d, Y') }}</p>
                    </div>
                     <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Repayment Cycle</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ ucfirst($loan->repayment_cycle ?? 'Monthly') }}</p>
                    </div>
                    
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Interest Rate</p>
                        @php $interestNaira = $loan->amount * (($loan->interest_rate ?? 0) / 100); @endphp
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">
                            {{ $loan->interest_rate ?? '0' }}% 
                            <span class="text-[10px] text-slate-400 font-medium">(₦{{ number_format($interestNaira, 2) }})</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Repayments</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $loan->num_repayments ?? '1' }} Installments</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Fees & Charges</p>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">₦{{ number_format(($loan->processing_fee ?? 0) + ($loan->insurance_fee ?? 0), 2) }}</p>
                    </div>
                     <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Paid Amount</p>
                        <p class="text-sm font-bold text-green-600">₦{{ number_format($paid, 2) }}</p>
                    </div>
                </div>

                <!-- List View (Table) -->
                <div x-show="view === 'list'" class="p-0">
                    <table class="w-full text-sm text-left">
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-medium text-slate-500">Loan Amount</td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white text-right">₦{{ number_format($loan->amount, 2) }}</td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-medium text-slate-500">Paid Amount</td>
                                <td class="px-6 py-4 font-bold text-green-600 text-right">₦{{ number_format($paid, 2) }}</td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-medium text-slate-500">Balance</td>
                                <td class="px-6 py-4 font-bold text-primary text-right">₦{{ number_format($balance, 2) }}</td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-medium text-slate-500">Repayment Cycle</td>
                                <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-300 text-right">{{ ucfirst($loan->repayment_cycle ?? 'Monthly') }}</td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-medium text-slate-500">Total Installments</td>
                                <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-300 text-right">{{ $loan->num_repayments ?? '1' }}</td>
                            </tr>
                            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-6 py-4 font-medium text-slate-500">Interest Rate</td>
                                <td class="px-6 py-4 font-bold text-slate-700 dark:text-slate-300 text-right">
                                    {{ $loan->interest_rate ?? '0' }}%
                                    <span class="text-[10px] text-slate-400 font-medium">(₦{{ number_format($loan->amount * (($loan->interest_rate ?? 0) / 100), 2) }})</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Payment Verifications -->
            @if($pendingProofs && $pendingProofs->isNotEmpty())
                <div class="bg-amber-50 border border-amber-200 rounded-2xl overflow-hidden shadow-sm animate-in fade-in slide-in-from-top-4 duration-500">
                    <div class="px-6 py-4 border-b border-amber-200 flex items-center justify-between bg-amber-100/50">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600">payments</span>
                            <h3 class="text-sm font-black text-amber-900 uppercase tracking-wider">Pending Payment Verifications</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-800 text-[10px] font-black uppercase">{{ $pendingProofs->count() }} Pending</span>
                    </div>
                    <div class="divide-y divide-amber-100">
                        @foreach($pendingProofs as $proof)
                            <div class="p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="size-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-amber-600">
                                        <span class="material-symbols-outlined">receipt_long</span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-lg font-black text-slate-900">₦{{ number_format($proof->amount, 2) }}</p>
                                            <span class="text-[10px] font-bold text-amber-700 bg-amber-200/50 px-2 py-0.5 rounded uppercase font-mono tracking-tighter">{{ $proof->reference_code }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 mt-1">
                                            <p class="text-xs text-slate-500 font-medium">Submitted {{ $proof->created_at->diffForHumans() }}</p>
                                            @if($proof->receipt_path)
                                                <a href="{{ $proof->receipt_url }}" target="_blank" class="flex items-center gap-1 text-[10px] font-black text-blue-600 uppercase hover:underline">
                                                    <span class="material-symbols-outlined text-sm">attachment</span> View Receipt
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <button wire:click="declineProof('{{ $proof->id }}')" wire:confirm="Are you sure you want to decline this payment proof?" class="flex-1 sm:flex-none px-4 py-2 bg-white text-red-600 border border-red-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-red-50 transition-all">
                                        Decline
                                    </button>
                                    <button wire:click="approveProof('{{ $proof->id }}')" wire:confirm="Confirm and record this ₦{{ number_format($proof->amount) }} payment?" class="flex-1 sm:flex-none px-6 py-2 bg-green-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-green-600/20 hover:bg-green-700 transition-all">
                                        Approve & Record
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Card -->
            @if(!in_array($loan->status, ['applied', 'verification_pending', 'declined']))
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                <button wire:click="openRepaymentsModal" class="flex flex-col items-center justify-center gap-2 p-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md hover:border-primary/50 hover:text-primary transition-all group h-32">
                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-primary">Repayments</span>
                </button>
                 <button wire:click="openScheduleModal" class="flex flex-col items-center justify-center gap-2 p-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md hover:border-primary/50 hover:text-primary transition-all group h-32">
                    <div class="size-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-2xl">calendar_month</span>
                    </div>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-primary">Schedule</span>
                </button>
                 <button wire:click="openCollateralModal" class="flex flex-col items-center justify-center gap-2 p-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md hover:border-primary/50 hover:text-primary transition-all group h-32">
                    <div class="size-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-primary">Collateral</span>
                </button>
                 <button wire:click="openFeesModal" class="flex flex-col items-center justify-center gap-2 p-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md hover:border-primary/50 hover:text-primary transition-all group h-32">
                    <div class="size-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-primary">Fees</span>
                </button>
                 <button wire:click="openCommentsModal" class="flex flex-col items-center justify-center gap-2 p-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md hover:border-primary/50 hover:text-primary transition-all group h-32">
                    <div class="size-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 group-hover:bg-slate-800 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-2xl">comment</span>
                    </div>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-primary">Comments</span>
                </button>
                 <button wire:click="$set('showDeleteModal', true)" class="flex flex-col items-center justify-center gap-2 p-4 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm hover:shadow-md hover:border-red-500/50 hover:text-red-500 transition-all group h-32">
                    <div class="size-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-2xl">delete</span>
                    </div>
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover:text-red-500">Delete Loan</span>
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Repayments Modal -->
    <div x-data="{ open: @entangle('showRepaymentsModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full h-full sm:h-auto sm:max-w-6xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#1a1f2b] sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Loan Repayments</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Manage schedule and history for #{{ $loan->loan_number }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('repayments.print', $loan->id) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                        <span class="material-symbols-outlined text-sm">print</span>
                        Print Statement
                    </a>
                    <button @click="open = false" class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-slate-500">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar" x-data="{ showForm: @entangle('showAddForm') }">
                @php
                    $totalInterest = $loan->amount * (($loan->interest_rate ?? 0) / 100);
                    $totalPayable = $loan->amount + $totalInterest;
                    $totalPaid = $loan->repayments->sum('amount');
                    $remainingBalance = max(0, $totalPayable - $totalPaid);
                @endphp

                <div class="flex justify-between items-center mb-8 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-8">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Payable</p>
                            <p class="text-sm font-black text-slate-900 dark:text-white">₦{{ number_format($totalPayable, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Paid</p>
                            <p class="text-sm font-black text-green-600">₦{{ number_format($totalPaid, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Balance</p>
                            <p class="text-sm font-black text-primary">₦{{ number_format($remainingBalance, 2) }}</p>
                        </div>
                        <div class="flex items-center">
                             <button @click="showForm = !showForm" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:scale-105 transition-all">
                                <span class="material-symbols-outlined text-sm" x-text="showForm ? 'remove' : 'add'">add</span>
                                <span x-text="showForm ? 'Hide Form' : 'Add Repayment'">Add Repayment</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Add Repayment Form -->
                    <div class="lg:col-span-1" x-show="showForm" x-collapse>
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800 h-full">
                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-6 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-lg">add_circle</span>
                                {{ $editingRepaymentId ? 'Edit' : 'New' }} Repayment Record
                            </h4>
                            
                            <div class="space-y-4">
                                <div class="p-3 bg-white dark:bg-[#1a1f2b] rounded-xl border border-slate-100 dark:border-slate-800 mb-4">
                                    <div class="flex justify-between text-[10px] font-black uppercase text-slate-400 mb-2">
                                        <span>Standard Split</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="text-center flex-1">
                                            <p class="text-[9px] font-bold text-slate-500 uppercase">Principal</p>
                                            <p class="text-xs font-black text-slate-900 dark:text-white">₦{{ number_format($suggestedPrincipal, 2) }}</p>
                                        </div>
                                        <div class="w-px h-6 bg-slate-100 dark:bg-slate-800"></div>
                                        <div class="text-center flex-1">
                                            <p class="text-[9px] font-bold text-slate-500 uppercase">Interest</p>
                                            <p class="text-xs font-black text-slate-900 dark:text-white">₦{{ number_format($suggestedInterest, 2) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Actual Amount Paid</label>
                                    <input wire:model="amount" type="number" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-black text-lg text-primary">
                                    @error('amount') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Payment Method</label>
                                    <select wire:model="payment_method" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white">
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cheque">Check</option>
                                        <option value="Online">Online Payment</option>
                                    </select>
                                </div>

                                <div x-data="{ openStaff: false, search: '' }">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Collected By</label>
                                    <div class="relative">
                                        <button @click="openStaff = !openStaff" type="button" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-left flex items-center justify-between">
                                            @if($collected_by)
                                                <span class="text-slate-900 dark:text-white">{{ $staffs->firstWhere('id', $collected_by)?->name }}</span>
                                            @else
                                                <span class="text-slate-400">Select Staff</span>
                                            @endif
                                            <span class="material-symbols-outlined text-slate-400">unfold_more</span>
                                        </button>
                                        
                                        <div x-show="openStaff" @click.outside="openStaff = false" class="absolute z-20 mt-2 w-full bg-white dark:bg-[#1a1f2b] rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden" style="display: none;">
                                            <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                                                <input x-model="search" type="text" placeholder="Search staff..." class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-bold focus:ring-0">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto py-1 custom-scrollbar">
                                                @foreach($staffs as $staff)
                                                    <button @click="$wire.set('collected_by', '{{ $staff->id }}'); openStaff = false" class="w-full px-4 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-3 transition-colors">
                                                        <div class="size-6 rounded-full bg-slate-200 bg-cover" style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($staff->name) }}')"></div>
                                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $staff->name }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @error('collected_by') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Collection Date</label>
                                    <input wire:model="paid_at" type="date" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white">
                                </div>

                                <button wire:click="{{ $editingRepaymentId ? 'saveRepayment' : 'addRepayment' }}" class="w-full py-4 bg-primary text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-primary/30 hover:scale-[1.02] active:scale-95 transition-all">
                                    {{ $editingRepaymentId ? 'Save Changes' : 'Submit Payment' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Repayments List -->
                    <div :class="showForm ? 'lg:col-span-2' : 'lg:col-span-3'">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">Repayment History</h4>
                        </div>

                        <div class="space-y-4">
                            @forelse($loan->repayments as $repayment)
                                <div class="bg-white dark:bg-[#1a1f2b] p-5 rounded-2xl border border-slate-100 dark:border-slate-800 hover:shadow-md transition-all group">
                                    <div class="flex flex-col sm:flex-row justify-between gap-4">
                                        <div class="flex items-start gap-4">
                                            <div class="size-12 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center shrink-0">
                                                <span class="material-symbols-outlined text-green-600 dark:text-green-400">check_circle</span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h5 class="text-lg font-black text-slate-900 dark:text-white">₦{{ number_format($repayment->amount, 2) }}</h5>
                                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 text-[9px] font-black uppercase">{{ $repayment->payment_method }}</span>
                                                </div>
                                                <p class="text-xs text-slate-500 font-medium mt-1">
                                                    Collected by <span class="font-bold text-slate-700 dark:text-slate-300">{{ $repayment->collector->name ?? 'System' }}</span> 
                                                    on <span class="font-bold">{{ $repayment->paid_at->format('M d, Y') }}</span>
                                                </p>
                                                <div class="flex flex-wrap gap-4 mt-3">
                                                    <div class="text-[10px] text-slate-400 font-bold uppercase">P: <span class="text-slate-600 dark:text-slate-300">₦{{ number_format($repayment->principal_amount, 2) }}</span></div>
                                                    <div class="text-[10px] text-slate-400 font-bold uppercase">I: <span class="text-slate-600 dark:text-slate-300">₦{{ number_format($repayment->interest_amount, 2) }}</span></div>
                                                    @if($repayment->extra_amount > 0)
                                                        <div class="text-[10px] text-emerald-500 font-black uppercase">Extra: <span>₦{{ number_format($repayment->extra_amount, 2) }}</span></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex sm:flex-col justify-end gap-2 shrink-0">
                                            <button wire:click="editRepayment('{{ $repayment->id }}')" class="px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase hover:bg-primary hover:text-white transition-all">Edit</button>
                                            <button wire:click="deleteRepayment('{{ $repayment->id }}')" wire:confirm="Are you sure you want to delete this repayment?" class="px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 text-[10px] font-black uppercase hover:bg-red-600 hover:text-white transition-all">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 flex flex-col items-center justify-center text-slate-400 bg-slate-50 dark:bg-slate-800/20 rounded-3xl border-2 border-dashed border-slate-100 dark:border-slate-800">
                                    <span class="material-symbols-outlined text-4xl mb-2 opacity-50">payments</span>
                                    <p class="text-sm font-bold">No repayment records found</p>
                                    <p class="text-xs">Click 'Add Repayment' to record a payment</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div x-data="{ open: @entangle('showScheduleModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         id="schedule-modal"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full h-full sm:h-auto sm:max-w-6xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#1a1f2b] sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Repayment Schedule</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Expected installments for #{{ $loan->loan_number }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('schedule.print', $loan->id) }}" target="_blank" class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                        <span class="material-symbols-outlined text-sm">print</span> Print Schedule
                    </a>
                    <button @click="open = false" class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-slate-500">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 py-3 font-black rounded-l-lg">#</th>
                                <th class="px-4 py-3 font-black">Due Date</th>
                                <th class="px-4 py-3 font-black text-right">Principal</th>
                                <th class="px-4 py-3 font-black text-right">Interest</th>
                                <th class="px-4 py-3 font-black text-right">Penalty</th>
                                <th class="px-4 py-3 font-black text-right">Total Due</th>
                                <th class="px-4 py-3 font-black text-center">Status</th>
                                <th class="px-4 py-3 font-black text-center rounded-r-lg">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($loan->scheduledRepayments as $schedule)
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-4 py-4 font-bold text-slate-500">{{ $schedule->installment_number }}</td>
                                    <td class="px-4 py-4 font-bold text-slate-900 dark:text-white">
                                        {{ $schedule->due_date->format('M d, Y') }}
                                        @if($schedule->due_date->isPast() && $schedule->status !== 'paid')
                                            <span class="text-[9px] text-red-500 font-black uppercase ml-1">(Overdue)</span>
                                        @endif
                                    </td>
                                    
                                    @if($editingScheduleId === $schedule->id)
                                        <td class="px-4 py-4 text-right">
                                            <input wire:model="schedulePrincipal" type="number" class="w-24 px-2 py-1 text-right text-xs font-bold border border-slate-200 rounded focus:ring-1 focus:ring-primary bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <input wire:model="scheduleInterest" type="number" class="w-24 px-2 py-1 text-right text-xs font-bold border border-slate-200 rounded focus:ring-1 focus:ring-primary bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <input wire:model="schedulePenalty" type="number" class="w-24 px-2 py-1 text-right text-xs font-bold border border-slate-200 rounded focus:ring-1 focus:ring-primary bg-white dark:bg-slate-800 dark:border-slate-700 dark:text-white">
                                        </td>
                                        <td class="px-4 py-4 text-right font-black text-slate-900 dark:text-white">
                                            ₦{{ number_format((float)($schedulePrincipal ?? 0) + (float)($scheduleInterest ?? 0) + (float)($schedulePenalty ?? 0), 2) }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="text-xs font-bold text-slate-400">Editing...</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="saveSchedule" class="p-1 rounded bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                                                    <span class="material-symbols-outlined text-lg">check</span>
                                                </button>
                                                <button wire:click="cancelEditSchedule" class="p-1 rounded bg-slate-100 text-slate-500 hover:bg-slate-200 transition-colors">
                                                    <span class="material-symbols-outlined text-lg">close</span>
                                                </button>
                                            </div>
                                        </td>
                                    @else
                                        <td class="px-4 py-4 text-right font-medium text-slate-600 dark:text-slate-300">₦{{ number_format($schedule->principal_amount, 2) }}</td>
                                        <td class="px-4 py-4 text-right font-medium text-slate-600 dark:text-slate-300">₦{{ number_format($schedule->interest_amount, 2) }}</td>
                                        <td class="px-4 py-4 text-right font-medium text-slate-600 dark:text-slate-300">
                                            @if($schedule->penalty_amount > 0)
                                                <span class="text-red-500">₦{{ number_format($schedule->penalty_amount, 2) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-right font-black text-slate-900 dark:text-white">
                                            ₦{{ number_format($schedule->principal_amount + $schedule->interest_amount + $schedule->penalty_amount, 2) }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-slate-100 text-slate-500',
                                                    'paid' => 'bg-green-100 text-green-600',
                                                    'partial' => 'bg-amber-100 text-amber-600',
                                                    'overdue' => 'bg-red-100 text-red-600',
                                                ];
                                            @endphp
                                            <span class="px-2 py-0.5 rounded {{ $statusColors[$schedule->status] ?? 'bg-slate-100 text-slate-500' }} text-[9px] font-black uppercase tracking-wider">
                                                {{ $schedule->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <button wire:click="editSchedule('{{ $schedule->id }}')" class="text-slate-400 hover:text-primary transition-colors">
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Fees Modal -->
    <div x-data="{ open: @entangle('showFeesModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         id="fees-modal"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full h-full sm:h-auto sm:max-w-2xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#1a1f2b] sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Loan Fees & Penalties</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Configuration for #{{ $loan->loan_number }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="open = false" class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined text-slate-500">close</span>
                    </button>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                <div class="space-y-6">
                    <!-- Standard Fees -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">payments</span>
                            Standard Fees
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Processing Fee (₦)</label>
                                <input wire:model="feeProcessing" type="number" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white">
                                @error('feeProcessing') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Insurance Fee (₦)</label>
                                <input wire:model="feeInsurance" type="number" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary/20 transition-all font-bold text-slate-900 dark:text-white">
                                @error('feeInsurance') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Penalty Configuration -->
                    <div class="bg-red-50 dark:bg-red-900/10 p-6 rounded-2xl border border-red-100 dark:border-red-900/30">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-sm font-black text-red-700 dark:text-red-400 uppercase tracking-wider flex items-center gap-2">
                                <span class="material-symbols-outlined">gavel</span>
                                Penalty Configuration
                            </h4>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider {{ $overridePenalty ? 'text-red-600' : 'text-slate-400' }}">Override System Default</span>
                                <button wire:click="$toggle('overridePenalty')" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $overridePenalty ? 'bg-red-600' : 'bg-slate-200 dark:bg-slate-700' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $overridePenalty ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>
                        </div>

                        <div x-show="$wire.overridePenalty" x-transition>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Penalty Amount/Rate</label>
                                    <input wire:model="feePenaltyValue" type="number" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-slate-900 dark:text-white">
                                    @error('feePenaltyValue') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Type</label>
                                    <select wire:model="feePenaltyType" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-slate-900 dark:text-white">
                                        <option value="fixed">Fixed Amount (₦)</option>
                                        <option value="percentage">Percentage (%)</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Frequency (Increase By)</label>
                                    <select wire:model="feePenaltyFrequency" class="w-full px-4 py-3 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-red-500/20 transition-all font-bold text-slate-900 dark:text-white">
                                        <option value="one_time">One Time (Flat)</option>
                                        <option value="daily">Daily Increase</option>
                                        <option value="weekly">Weekly Increase</option>
                                        <option value="monthly">Monthly Increase</option>
                                        <option value="yearly">Yearly Increase</option>
                                    </select>
                                </div>
                            </div>
                            <p class="text-[10px] text-red-500 font-medium mt-3 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">info</span>
                                This will override the global system penalty settings for this specific loan.
                            </p>
                        </div>
                        <div x-show="!$wire.overridePenalty" class="text-center py-8">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">settings_suggest</span>
                            <p class="text-xs font-bold text-slate-500">Using System Default Settings</p>
                            <p class="text-[10px] text-slate-400 mt-1">Penalty calculation is managed by global rules.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-[#1a1f2b] flex justify-end gap-3 sticky bottom-0 z-10">
                <button @click="open = false" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-white transition-all">Cancel</button>
                <button wire:click="saveFees" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-xs shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">Save Changes</button>
            </div>
        </div>
    </div>
    <!-- Comments Modal -->
    <div x-data="{ open: @entangle('showCommentsModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         id="comments-modal"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full h-full sm:h-auto sm:max-w-2xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#1a1f2b] sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Loan Discussion</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Staff comments for #{{ $loan->loan_number }}</p>
                </div>
                <button @click="open = false" class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-slate-500">close</span>
                </button>
            </div>

            <!-- Modal Content (Chat) -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar bg-slate-50 dark:bg-slate-900/50">
                <div class="space-y-6">
                    @forelse($loan->comments as $comment)
                        @php
                            $isMe = $comment->user_id === auth()->id();
                        @endphp
                        <div class="flex gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                            <div class="shrink-0">
                                <div class="size-8 rounded-full bg-slate-200 bg-cover border-2 border-white dark:border-slate-800 shadow-sm" style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=random')"></div>
                            </div>
                            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%]">
                                <div class="flex items-baseline gap-2 mb-1">
                                    <span class="text-[10px] font-bold text-slate-900 dark:text-white">{{ $comment->user->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-medium">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="px-4 py-3 rounded-2xl text-sm font-medium {{ $isMe ? 'bg-primary text-white rounded-tr-none' : 'bg-white dark:bg-[#1a1f2b] text-slate-700 dark:text-slate-300 border border-slate-100 dark:border-slate-800 shadow-sm rounded-tl-none' }}">
                                    {{ $comment->body }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <span class="material-symbols-outlined text-4xl mb-2 opacity-50">forum</span>
                            <p class="text-xs font-bold">No comments yet.</p>
                            <p class="text-[10px]">Start the discussion about this loan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Modal Footer (Input) -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-[#1a1f2b] sticky bottom-0 z-10">
                <form wire:submit.prevent="postComment" class="flex flex-col gap-3">
                    <div class="flex justify-end">
                        <button type="submit" class="flex items-center gap-2 px-6 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/30 hover:bg-blue-700 transition-all">
                            <span class="material-symbols-outlined text-lg">send</span>
                            Send
                        </button>
                    </div>
                    <textarea wire:model="newComment" rows="3" placeholder="Type your comment here..." class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary/20 custom-scrollbar"></textarea>
                </form>
            </div>
        </div>
    </div>
    <!-- Collateral Modal -->
    <div x-data="{ open: @entangle('showCollateralModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         id="collateral-modal"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full h-full sm:h-auto sm:max-w-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-white dark:bg-[#1a1f2b] sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Collateral Details</h3>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">Asset securing Loan #{{ $loan->loan_number }}</p>
                </div>
                <button @click="open = false" class="size-10 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-slate-500">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                @if($loan->collateral)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Image Section -->
                        <div>
                            <div class="aspect-video rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700 relative">
                                @if($loan->collateral->image_url)
                                    <img src="{{ $loan->collateral->image_url }}" alt="{{ $loan->collateral->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined text-6xl opacity-50">image</span>
                                        <span class="text-xs font-bold uppercase mt-2">No Image Available</span>
                                    </div>
                                @endif
                                <div class="absolute top-4 right-4">
                                    <span class="px-3 py-1 rounded-lg bg-white/90 dark:bg-black/50 backdrop-blur-sm text-xs font-black uppercase tracking-wider {{ $loan->collateral->status === 'in_vault' ? 'text-green-600' : 'text-slate-500' }}">
                                        {{ str_replace('_', ' ', $loan->collateral->status) }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($loan->collateral->documents)
                                <div class="mt-6">
                                    <h5 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider mb-3">Documents</h5>
                                    <div class="space-y-2">
                                        @foreach($loan->collateral->documents as $doc)
                                            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                                <div class="size-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                                                    <span class="material-symbols-outlined text-lg">description</span>
                                                </div>
                                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $doc }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Details Section -->
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-2xl font-black text-slate-900 dark:text-white">{{ $loan->collateral->name }}</h4>
                                <p class="text-sm text-slate-500 font-medium mt-1">{{ $loan->collateral->description }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Asset Value</p>
                                    <p class="text-lg font-black text-slate-900 dark:text-white">₦{{ number_format($loan->collateral->value, 2) }}</p>
                                </div>
                                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">LTV Ratio</p>
                                    @php
                                        $ltv = $loan->collateral->value > 0 ? ($loan->amount / $loan->collateral->value) * 100 : 0;
                                        $ltvColor = $ltv > 80 ? 'text-red-500' : ($ltv > 50 ? 'text-amber-500' : 'text-green-500');
                                    @endphp
                                    <p class="text-lg font-black {{ $ltvColor }}">{{ number_format($ltv, 1) }}%</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Type</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $loan->collateral->type }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Condition</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $loan->collateral->condition ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-100 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-500 uppercase">Registered Date</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $loan->collateral->registered_date ? $loan->collateral->registered_date->format('M d, Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="size-20 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-4xl text-amber-500">inventory_2</span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">No Collateral Found</h4>
                        <p class="text-sm text-slate-500 max-w-xs mx-auto mb-8">This loan is currently unsecured. Add collateral to reduce risk and activate asset tracking.</p>
                        <button wire:click="goToAddCollateral" class="px-6 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-lg shadow-primary/30 hover:scale-105 transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">add_circle</span>
                            Add Collateral
                        </button>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            @if($loan->collateral)
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-[#1a1f2b] flex justify-end gap-3 sticky bottom-0 z-10">
                <button wire:click="deleteCollateral" wire:confirm="Are you sure you want to delete this collateral record?" class="px-5 py-2.5 rounded-xl bg-red-50 text-red-600 font-bold text-xs hover:bg-red-100 transition-all">Delete Asset</button>
                <button wire:click="goToAddCollateral" class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-300 transition-all">Edit Details</button>
            </div>
            @endif
        </div>
    </div>
    <!-- Delete Modal -->
    <div x-data="{ open: @entangle('showDeleteModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         id="delete-modal"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-0 sm:p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-red-900/80 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden text-center p-8">
            <div class="size-20 rounded-full bg-red-100 mx-auto flex items-center justify-center mb-6 animate-pulse">
                <span class="material-symbols-outlined text-5xl text-red-600">warning</span>
            </div>
            
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Delete Loan Record?</h3>
            <p class="text-sm text-slate-500 font-medium mb-8">
                You are about to permanently delete <strong class="text-slate-900 dark:text-white">Loan #{{ $loan->loan_number }}</strong>. 
                <br>This action cannot be undone. All linked repayment history, comments, and logs will be erased.
            </p>

            <div class="grid grid-cols-2 gap-4">
                <button @click="open = false" class="py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    Cancel
                </button>
                <button wire:click="deleteLoan" class="py-3 rounded-xl bg-red-600 text-white font-bold shadow-lg shadow-red-600/30 hover:bg-red-700 hover:scale-[1.02] active:scale-95 transition-all">
                    Yes, Delete Loan
                </button>
            </div>
        </div>
    </div>

    <livewire:borrower.message-modal :borrower="$loan->borrower" />
</div>

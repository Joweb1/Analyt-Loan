<div>
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('collections') }}" class="size-10 flex items-center justify-center bg-white dark:bg-[#1a1f2b] rounded-xl border border-slate-100 dark:border-slate-800 text-slate-500 hover:text-primary transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Collection Entry</h1>
        
        <div class="ml-auto flex items-center gap-3">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Show All Active</span>
            <button wire:click="toggleFilter" type="button" 
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                :class="{ 'bg-primary': @json($showAllActive), 'bg-slate-200 dark:bg-slate-700': !@json($showAllActive) }"
                role="switch">
                <span class="sr-only">Toggle Filter</span>
                <span aria-hidden="true" 
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="{ 'translate-x-5': @json($showAllActive), 'translate-x-0': !@json($showAllActive) }">
                </span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 mb-6">
        <div class="flex items-center gap-4 mb-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by Loan ID, Name, Phone, staff:name..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Loan / Customer</th>
                        <th class="px-4 py-3">Assigned Staff</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Balance</th>
                        <th class="px-4 py-3 text-right rounded-r-xl">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-4">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $loan->loan_number }}</p>
                                <p class="text-xs text-slate-500">{{ $loan->borrower->user->name }} | {{ $loan->borrower->phone }}</p>
                            </td>
                            <td class="px-4 py-4">
                                {{ $loan->loanOfficer->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-4 py-4 font-medium text-slate-900 dark:text-white">
                                ₦{{ number_format($loan->amount, 2) }}
                            </td>
                            <td class="px-4 py-4 font-medium text-slate-900 dark:text-white">
                                ₦{{ number_format($loan->balance, 2) }}
                                @if($loan->status === 'overdue')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 ml-2">Overdue</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <button wire:click="selectLoan('{{ $loan->id }}')" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-primary/90 transition-colors">
                                    Add Repayment
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                No loans found. (Default shows overdue loans)
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $loans->links() }}
        </div>
    </div>

    <!-- Repayment Modal -->
    @if($showRepaymentModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-3xl p-6 w-full max-w-md shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Repayment</h3>
                    <button wire:click="$set('showRepaymentModal', false)" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form wire:submit.prevent="addRepayment" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Amount (₦)</label>
                        <input wire:model="amount" type="number" step="0.01" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-bold" required>
                        @error('amount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Method</label>
                            <select wire:model="payment_method" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="POS">POS</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                            @error('payment_method') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Date</label>
                            <input wire:model="paid_at" type="date" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40" required>
                            @error('paid_at') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Collected By</label>
                        <select wire:model="collected_by" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40" required>
                            <option value="">Select Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                        @error('collected_by') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" wire:click="$set('showRepaymentModal', false)" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">save</span>
                            Save Repayment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

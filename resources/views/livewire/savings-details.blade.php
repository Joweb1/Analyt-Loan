<div class="w-full mx-auto space-y-8 p-0">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-1 lg:px-2 pt-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                <a href="{{ route('borrowers.index') }}" class="hover:text-primary transition-colors">Customers</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-white">Savings Details</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Savings Account</h2>
        </div>
        <div class="flex gap-3">
            @can('export_and_print')
                <a href="{{ route('savings.print', $borrower->id) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-xl text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                    <span class="material-symbols-outlined text-lg">print</span>
                    Statement
                </a>
            @endcan
            <button wire:click="openTransactionModal('deposit')" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-green-600/30 hover:bg-green-700 transition-all">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                Add Deposit
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-1 lg:px-2 pb-8">
        <!-- Left Column: Customer Card -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden group hover:shadow-md transition-all duration-300">
                <div class="p-6 relative">
                    <div class="absolute top-4 right-4">
                        <span class="px-2 py-1 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-[10px] font-black uppercase tracking-wider">Active Saver</span>
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
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Account Number</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $savingsAccount->account_number }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">Phone</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $borrower->phone ?? 'N/A' }}</span>
                        </div>
                         <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">BVN</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white font-mono">{{ $borrower->bvn ?? 'N/A' }}</span>
                        </div>
                        <a href="{{ route('borrower.profile', $borrower->id) }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-primary/10 text-primary hover:bg-primary/20 transition-colors text-xs font-bold w-full mt-4">
                            <span class="material-symbols-outlined text-sm">account_circle</span> View User Profile
                        </a>
                    </div>
                    
                    <div class="mt-8 grid grid-cols-2 gap-2">
                        @can('communicate_with_customers')
                            <a href="tel:{{ $borrower->phone }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">call</span> Call
                            </a>
                            <a href="sms:{{ $borrower->phone }}" class="flex items-center justify-center gap-2 py-2.5 rounded-xl bg-green-50 text-green-600 hover:bg-green-100 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">sms</span> SMS
                            </a>
                        @endcan
                        @can('send_customer_messages')
                            <button wire:click="$dispatchTo('borrower.message-modal', 'openMessageModal', { borrowerId: '{{ $borrower->id }}' })" class="col-span-2 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-primary/20 text-primary hover:bg-primary/5 transition-colors text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">chat_bubble</span> Send Message
                            </button>
                        @endcan
                        <button wire:click="openTransactionModal('withdrawal')" class="col-span-2 flex items-center justify-center gap-2 py-2.5 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition-colors text-xs font-bold">
                            <span class="material-symbols-outlined text-sm">account_balance_wallet</span> Request Withdrawal
                        </button>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-500 uppercase">Account Status</span>
                    <span class="flex items-center gap-1 text-[10px] font-black uppercase text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-xs">check_circle</span> Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Savings Overview & History -->
        <div class="md:col-span-2 space-y-8">
            <!-- Balance Card -->
             <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden bg-gradient-to-br from-white to-green-50/30 dark:from-[#1a1f2b] dark:to-green-900/10">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Current Balance</p>
                            <h3 class="text-4xl font-black text-slate-900 dark:text-white">₦{{ $savingsAccount->balance->format() }}</h3>
                        </div>
                        <div class="size-14 rounded-2xl bg-green-600 flex items-center justify-center text-white shadow-lg shadow-green-600/20">
                            <span class="material-symbols-outlined text-3xl">savings</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Deposits</p>
                            @php
                                $currency = $savingsAccount->balance->getCurrency();
                                $depositsMinor = (int) $savingsAccount->transactions()->where('type', 'deposit')->sum('amount');
                                $deposits = new \App\ValueObjects\Money($depositsMinor, $currency);
                            @endphp
                            <p class="text-sm font-black text-slate-700 dark:text-slate-200">₦{{ $deposits->format() }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Total Withdrawals</p>
                            @php
                                $withdrawalsMinor = (int) $savingsAccount->transactions()->where('type', 'withdrawal')->sum('amount');
                                $withdrawals = new \App\ValueObjects\Money($withdrawalsMinor, $currency);
                            @endphp
                            <p class="text-sm font-black text-slate-700 dark:text-slate-200">₦{{ $withdrawals->format() }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Interest Rate</p>
                            <p class="text-sm font-black text-green-600">{{ $savingsAccount->interest_rate }}% P.A</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Est. Interest</p>
                            <p class="text-sm font-black text-slate-700 dark:text-slate-200">₦{{ $savingsAccount->balance->multiply($savingsAccount->interest_rate / 100)->format() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Transaction History</h3>
                    <div class="flex gap-2">
                         <button class="p-2 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">filter_list</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[10px] text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-6 py-4 font-black">Date</th>
                                <th class="px-6 py-4 font-black">Reference</th>
                                <th class="px-6 py-4 font-black">Description</th>
                                <th class="px-6 py-4 font-black text-right">Amount</th>
                                <th class="px-6 py-4 font-black text-center">Status</th>
                                <th class="px-6 py-4 font-black text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($transactions as $transaction)
                                <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $transaction->transaction_date->format('M d, Y') }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium">{{ $transaction->transaction_date->format('h:i A') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-slate-500 uppercase">{{ $transaction->reference }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ ucfirst($transaction->type) }}</div>
                                        <div class="text-[10px] text-slate-400 max-w-[200px] truncate">{{ $transaction->notes ?? 'No notes' }}</div>
                                        <div class="text-[9px] text-slate-400 italic">By {{ $transaction->staff->name ?? 'System' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black {{ $transaction->type === 'deposit' ? 'text-green-600' : 'text-red-500' }}">
                                            {{ $transaction->type === 'deposit' ? '+' : '-' }}₦{{ $transaction->amount->format() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-600 text-[9px] font-black uppercase tracking-wider">Completed</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @can('delete_savings')
                                            @if(!$transaction->repayment_id)
                                                <button 
                                                    wire:click="deleteTransaction('{{ $transaction->id }}')" 
                                                    wire:confirm="Are you sure you want to delete this transaction? This will also revert the account balance."
                                                    class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"
                                                    title="Delete Transaction"
                                                >
                                                    <span class="material-symbols-outlined text-lg">delete</span>
                                                </button>
                                            @else
                                                <span class="material-symbols-outlined text-sm text-slate-300 cursor-help" title="Linked to loan repayment - Cannot delete from here">lock</span>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">history</span>
                                        <p class="text-sm font-bold">No transactions recorded yet</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/20">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div x-data="{ open: @entangle('showTransactionModal') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] overflow-hidden flex items-center justify-center p-4" 
         style="display: none;">
        
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        
        <div class="relative bg-white dark:bg-[#1a1f2b] w-full max-w-md rounded-3xl shadow-2xl flex flex-col overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-900 dark:text-white">New {{ ucfirst($transactionType) }}</h3>
                <button @click="open = false" class="size-8 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-slate-500">close</span>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Amount (₦)</label>
                        <input wire:model="amount" type="number" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-green-500/20 transition-all font-black text-2xl text-green-600" placeholder="0.00">
                        @error('amount') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Transaction Date</label>
                        <input wire:model="transactionDate" type="date" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-green-500/20 transition-all font-bold text-slate-900 dark:text-white">
                        @error('transactionDate') <span class="text-[10px] font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Reference (Optional)</label>
                        <input wire:model="reference" type="text" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-green-500/20 transition-all font-mono text-xs uppercase" readonly>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1 px-1">Notes/Description</label>
                        <textarea wire:model="notes" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl focus:ring-2 focus:ring-green-500/20 transition-all text-sm font-medium" placeholder="E.g. Monthly savings contribution..."></textarea>
                    </div>
                </div>

                <button wire:click="submitTransaction" class="w-full mt-8 py-4 bg-green-600 text-white rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-green-600/30 hover:scale-[1.02] active:scale-95 transition-all">
                    Confirm {{ ucfirst($transactionType) }}
                </button>
            </div>
        </div>
    </div>

    <livewire:borrower.message-modal :borrower="$borrower" />
</div>

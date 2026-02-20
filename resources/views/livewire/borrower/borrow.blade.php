<div class="min-h-screen bg-white p-6 pb-32">
    @if($showSuccess)
        <div class="fixed inset-0 z-50 bg-white flex flex-col items-center justify-center p-8 text-center animate-in fade-in">
            <div class="size-24 bg-green-100 rounded-full flex items-center justify-center text-green-600 mb-6">
                <span class="material-symbols-outlined text-5xl">check_circle</span>
            </div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Application Sent!</h2>
            <p class="text-slate-500 mb-8">Your loan application for ₦{{ number_format($amount) }} has been submitted successfully. We will notify you once it's approved.</p>
            <a href="{{ route('borrower.home') }}" wire:navigate class="w-full bg-brand text-white font-bold py-4 rounded-xl shadow-lg">
                Back to Home
            </a>
        </div>
    @else
        <h1 class="text-2xl font-bold text-slate-900 mb-6">Borrow Money</h1>

        <!-- Loan Product Selection -->
        <div class="mb-8">
            <label class="block text-sm font-bold text-slate-500 mb-3 uppercase tracking-widest text-[10px]">1. Select Loan Product</label>
            <div class="flex flex-col gap-3">
                @foreach($loanProducts as $product)
                    <button 
                        wire:click="selectProduct('{{ $product->id }}')" 
                        class="p-4 rounded-2xl border-2 text-left transition-all {{ $selectedProduct && $selectedProduct->id === $product->id ? 'border-brand bg-brand-soft shadow-sm' : 'border-slate-100 bg-white' }}"
                    >
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-900">{{ $product->name }}</span>
                            @if($selectedProduct && $selectedProduct->id === $product->id)
                                <span class="material-symbols-outlined text-brand">check_circle</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $product->description ?: 'Standard lending product' }}</p>
                        <div class="mt-2 flex gap-3">
                            <span class="text-[10px] font-black uppercase tracking-tighter text-brand bg-white px-2 py-0.5 rounded border border-brand/10">{{ $product->default_interest_rate }}% Rate</span>
                            <span class="text-[10px] font-black uppercase tracking-tighter text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-100">{{ $product->default_duration }} {{ Str::plural($product->duration_unit, $product->default_duration) }}</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Amount Slider -->
        <div class="mb-8">
            <label class="block text-sm font-bold text-slate-500 mb-4 uppercase tracking-widest text-[10px]">2. How much do you need?</label>
            <div class="text-center mb-6">
                <span class="text-5xl font-black text-slate-900">₦{{ number_format($amount) }}</span>
            </div>
            <input type="range" min="1000" max="{{ $creditLimit }}" step="1000" wire:model.live="amount" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-brand">
            <div class="flex justify-between mt-2 text-xs font-bold text-slate-400">
                <span>₦1,000</span>
                <span>₦{{ number_format($creditLimit) }}</span>
            </div>
        </div>

        <!-- Repayment Cycle & Duration -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 mb-2 uppercase tracking-widest">Duration</label>
                <div class="flex items-center gap-2">
                    <input type="number" wire:model.live="duration" class="w-20 rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-sm font-bold">
                    <select wire:model.live="duration_unit" class="flex-1 rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-xs font-bold bg-white">
                        <option value="day">Days</option>
                        <option value="week">Weeks</option>
                        <option value="month">Months</option>
                        <option value="year">Years</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold text-slate-500 mb-2 uppercase tracking-widest">Repayment Cycle</label>
                <select wire:model.live="repayment_cycle" class="w-full rounded-xl border-slate-200 focus:border-brand focus:ring-brand text-xs font-bold bg-white h-[42px]">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="biweekly">Bi-weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="bg-slate-50 rounded-3xl p-6 border border-slate-100 mb-8">
            <div class="flex justify-between items-center mb-4">
                <span class="text-slate-500 text-xs font-medium">Interest ({{ $interest_rate }}%)</span>
                <span class="font-bold text-slate-900 text-sm">₦{{ number_format($this->calculated['interest'], 2) }}</span>
            </div>
             <div class="flex justify-between items-center mb-4">
                <span class="text-slate-500 text-xs font-medium">Total Installments</span>
                <span class="font-bold text-slate-900 text-sm">{{ $this->calculated['num_installments'] }} payments</span>
            </div>
            <div class="h-px bg-slate-200 my-4"></div>
            <div class="flex justify-between items-center">
                <span class="text-slate-900 font-bold">Total Repayment</span>
                <span class="text-xl font-black text-brand">₦{{ number_format($this->calculated['total'], 2) }}</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 mt-2 text-right uppercase tracking-wider">₦{{ number_format($this->calculated['installment_amount'], 2) }} per {{ str_replace('ly', '', $repayment_cycle) }}</p>
        </div>

        <button wire:click="openBreakdown" class="w-full bg-brand text-white font-bold text-lg py-4 rounded-2xl shadow-lg shadow-brand/20 hover:opacity-90 transition-colors">
            Review Application
        </button>

        <!-- Breakdown Modal -->
        @if($showBreakdown)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-in fade-in duration-300">
                <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl animate-in zoom-in-95 duration-300 flex flex-col max-h-[90vh]">
                    <div class="flex justify-between items-center mb-6 shrink-0">
                        <h3 class="text-xl font-bold text-slate-900">Loan Breakdown</h3>
                        <button wire:click="$set('showBreakdown', false)" class="p-2 bg-slate-100 rounded-full hover:bg-slate-200">
                            <span class="material-symbols-outlined text-slate-600">close</span>
                        </button>
                    </div>

                    <div class="overflow-y-auto pr-2 custom-scrollbar">
                        <div class="space-y-4 mb-8">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Principal Amount</span>
                                <span class="font-bold">₦{{ number_format($amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Interest ({{ $interest_rate }}%)</span>
                                <span class="font-bold">₦{{ number_format($this->calculated['interest'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500 font-bold">Total Payable</span>
                                <span class="font-black text-brand">₦{{ number_format($this->calculated['total'], 2) }}</span>
                            </div>
                            
                            <div class="pt-4 border-t border-slate-100">
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Repayment Schedule</p>
                                <div class="space-y-3">
                                    @foreach($this->calculated['schedule'] as $item)
                                        <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase">Installment {{ $item['installment'] }}</p>
                                                <p class="text-xs font-bold text-slate-700">{{ $item['due_date'] }}</p>
                                            </div>
                                            <span class="font-black text-slate-900 text-sm">₦{{ number_format($item['amount'], 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-auto">
                        <div class="bg-brand-soft p-4 rounded-2xl text-[10px] text-brand leading-relaxed mb-4">
                            By clicking "Confirm & Apply", you agree to the terms of the {{ $selectedProduct->name }} and authorize the repayment schedule above.
                        </div>
                        <button wire:click="submitApplication" class="w-full bg-brand text-white font-bold py-4 rounded-xl hover:opacity-90 shadow-lg shadow-brand/20">
                            Confirm & Apply
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

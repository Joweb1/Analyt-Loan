<div class="p-0 max-w-7xl mx-auto w-full">
    <!-- Page Heading -->
    <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight text-primary dark:text-white">Loan Products</h2>
            <p class="text-[#716b80] text-base font-medium">Configure different loan types and their default terms.</p>
        </div>
        <button wire:click="$set('showModal', true)" class="flex items-center gap-2 bg-primary dark:bg-zinc-100 dark:text-primary text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform active:scale-95">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            <span>Add Loan Product</span>
        </button>
    </div>

    <x-settings-nav active="loan-products" />

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-[#dfdee3] dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-primary/5 dark:bg-zinc-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Product Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Default Rate</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-center">Default Duration</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Repayment Cycle</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[#dfdee3] dark:divide-zinc-800">
                @foreach($products as $product)
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <p class="text-sm font-bold dark:text-white">{{ $product->name }}</p>
                                <p class="text-xs text-[#716b80] truncate max-w-xs">{{ $product->description }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <span class="text-sm font-bold dark:text-white">{{ $product->default_interest_rate }}%</span>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="text-sm font-bold dark:text-white">{{ $product->default_duration }} {{ Str::plural($product->duration_unit, $product->default_duration) }}</span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full text-xs font-bold">
                                {{ ucfirst($product->repayment_cycle) }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="edit('{{ $product->id }}')" class="p-2 text-[#716b80] hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button wire:click="delete('{{ $product->id }}')" class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-[#dfdee3] dark:border-zinc-800">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-[520px] rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                <div class="px-8 pt-8 pb-4 flex justify-between items-start">
                    <div>
                        <h2 class="text-primary dark:text-white text-2xl font-black">{{ $productId ? 'Edit' : 'Add' }} Loan Product</h2>
                        <p class="text-gray-500 text-sm">Define default terms for this product.</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-primary">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="px-8 py-4 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Product Name</label>
                            <input wire:model="name" type="text" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" placeholder="e.g. Personal Loan">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Description</label>
                            <textarea wire:model="description" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" rows="2"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Default Rate (%)</label>
                                <input wire:model="default_interest_rate" type="number" step="0.01" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase">Default Duration</label>
                                <div class="flex gap-2">
                                    <input wire:model="default_duration" type="number" class="w-1/2 rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                    <select wire:model="duration_unit" class="w-1/2 rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white text-xs">
                                        <option value="day">Days</option>
                                        <option value="week">Weeks</option>
                                        <option value="month">Months</option>
                                        <option value="year">Years</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Repayment Cycle</label>
                            <select wire:model="repayment_cycle" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="px-8 pb-8 pt-4">
                    <button wire:click="save" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg hover:scale-[1.02] transition-transform">
                        Save Loan Product
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

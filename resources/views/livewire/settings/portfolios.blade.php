<div class="p-0 max-w-7xl mx-auto w-full">
    <!-- Page Heading -->
    <div class="flex flex-wrap justify-between items-end gap-4 mb-8">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight text-primary dark:text-white">Portfolio Management</h2>
            <p class="text-[#716b80] text-base font-medium">Group your borrowers into portfolios and assign staff to manage them.</p>
        </div>
        <button wire:click="$set('showModal', true)" class="flex items-center gap-2 bg-primary dark:bg-zinc-100 dark:text-primary text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg shadow-primary/20 hover:scale-[1.02] transition-transform active:scale-95">
            <span class="material-symbols-outlined text-[20px]">add_circle</span>
            <span>Create Portfolio</span>
        </button>
    </div>

    <x-settings-nav active="portfolios" />

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-[#dfdee3] dark:border-zinc-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                <tr class="bg-primary/5 dark:bg-zinc-800/50">
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Portfolio Name</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider">Stats</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-center">Staff</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">PnL</th>
                    <th class="px-6 py-4 text-xs font-bold text-primary dark:text-white uppercase tracking-wider text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-[#dfdee3] dark:divide-zinc-800">
                @foreach($portfolios as $portfolio)
                    <tr class="hover:bg-background-light/50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <p class="text-sm font-bold dark:text-white">{{ $portfolio->name }}</p>
                                <p class="text-xs text-[#716b80] truncate max-w-xs">{{ $portfolio->description }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-medium text-gray-500">Balance: <b class="text-primary dark:text-white">₦{{ $portfolio->portfolio_balance->format() }}</b></span>
                                <span class="text-xs font-medium text-gray-500">PAR: <b class="text-red-500">{{ $portfolio->par_percentage }}%</b></span>
                                <span class="text-xs font-medium text-gray-500">Borrowers: <b class="dark:text-white">{{ $portfolio->borrowers->count() }}</b></span>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <div class="flex items-center justify-center -space-x-2">
                                @foreach($portfolio->staff->take(3) as $member)
                                    <div class="w-8 h-8 rounded-full bg-primary/10 border-2 border-white dark:border-zinc-900 flex items-center justify-center text-[10px] font-bold text-primary" title="{{ $member->name }}">
                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                    </div>
                                @endforeach
                                @if($portfolio->staff->count() > 3)
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-zinc-800 border-2 border-white dark:border-zinc-900 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                        +{{ $portfolio->staff->count() - 3 }}
                                    </div>
                                @endif
                                @if($portfolio->staff->isEmpty())
                                    <span class="text-xs text-gray-400 italic">None</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-sm font-bold {{ $portfolio->profit_loss->getMinorAmount() >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₦{{ $portfolio->profit_loss->format() }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="edit('{{ $portfolio->id }}')" class="p-2 text-[#716b80] hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <button wire:click="delete('{{ $portfolio->id }}')" class="p-2 text-[#716b80] hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
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
            {{ $portfolios->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="bg-white dark:bg-zinc-900 w-full max-w-[600px] rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 pt-8 pb-4 flex justify-between items-start border-b dark:border-zinc-800">
                    <div>
                        <h2 class="text-primary dark:text-white text-2xl font-black">{{ $portfolioId ? 'Edit' : 'Create' }} Portfolio</h2>
                        <p class="text-gray-500 text-sm">Manage portfolio details, staff, and borrowers.</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-primary">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="px-8 py-6 space-y-6 overflow-y-auto">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Portfolio Name</label>
                            <input wire:model="name" type="text" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" placeholder="e.g. Retail Portfolio">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
                            <textarea wire:model="description" class="w-full rounded-lg border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" rows="2" placeholder="Brief purpose of this portfolio..."></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Assign Staff Members</label>
                            <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 border rounded-lg dark:border-zinc-700">
                                @foreach($allStaff as $staff)
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-md cursor-pointer transition-colors">
                                        <input type="checkbox" wire:model="staffIds" value="{{ $staff->id }}" class="rounded text-primary border-gray-300">
                                        <span class="text-xs font-medium dark:text-white">{{ $staff->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Add Borrowers to Portfolio</label>
                            <div class="mb-2">
                                <input wire:model.live.debounce.300ms="searchBorrower" type="text" class="w-full rounded-lg text-sm border-gray-200 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white" placeholder="Search borrowers by name...">
                            </div>
                            <div class="grid grid-cols-1 gap-1 max-h-48 overflow-y-auto p-2 border rounded-lg dark:border-zinc-700">
                                @forelse($availableBorrowers as $borrower)
                                    <label class="flex items-center gap-2 p-2 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-md cursor-pointer transition-colors">
                                        <input type="checkbox" wire:model="selectedBorrowerIds" value="{{ $borrower->id }}" class="rounded text-primary border-gray-300">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold dark:text-white">{{ $borrower->user->name }}</span>
                                            <span class="text-[10px] text-gray-500">{{ $borrower->phone }} | ID: {{ $borrower->custom_id }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-[10px] text-gray-400 italic p-2 text-center">No unassigned borrowers found.</p>
                                @endforelse
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1">* Moving a borrower will also move all their historical loan data to this portfolio.</p>
                        </div>
                    </div>
                </div>

                <div class="px-8 pb-8 pt-4 border-t dark:border-zinc-800">
                    <button wire:click="save" class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg hover:scale-[1.02] transition-transform active:scale-95">
                        Save Portfolio Configuration
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

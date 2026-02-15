<div class="w-full max-w-4xl mx-auto py-4 px-2 sm:px-0 space-y-6 sm:space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">System Activities</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Audit log of actions across the platform</p>
        </div>
        <div class="flex">
            <button wire:click="markAllAsRead" class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-white rounded-xl text-xs font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-95">
                <span class="material-symbols-outlined text-sm">done_all</span>
                Mark all as read
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex gap-2 overflow-x-auto pb-2 -mx-2 px-2 sm:mx-0 sm:px-0 custom-scrollbar no-scrollbar">
        @foreach(['all' => 'All Activity', 'loan' => 'Loans', 'borrower' => 'Customers', 'collateral' => 'Collateral', 'payment' => 'Payments'] as $val => $label)
            <button 
                wire:click="$set('filter', '{{ $val }}')"
                class="px-4 py-2 rounded-full text-[10px] sm:text-xs font-bold whitespace-nowrap transition-all border {{ $filter === $val ? 'bg-primary text-white border-primary shadow-md shadow-primary/20' : 'bg-white dark:bg-[#1a1f2b] text-slate-500 border-slate-200 dark:border-slate-800 hover:border-primary/50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- Activity List -->
    <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($notifications as $n)
                @php
                    $icon = match($n->category) {
                        'loan' => 'monetization_on',
                        'borrower' => 'person',
                        'collateral' => 'inventory_2',
                        'payment' => 'payments',
                        default => 'notifications',
                    };
                    $color = match($n->type) {
                        'success' => 'green',
                        'danger' => 'red',
                        'warning' => 'amber',
                        'info' => 'blue',
                        default => 'slate',
                    };
                @endphp
                <div class="p-4 sm:p-6 flex items-start gap-3 sm:gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ $n->read_at ? 'opacity-60' : '' }}">
                    <div class="size-9 sm:size-12 rounded-xl bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 flex items-center justify-center shrink-0 border border-{{ $color }}-100 dark:border-{{ $color }}-800">
                        <span class="material-symbols-outlined text-lg sm:text-2xl text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $icon }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-2">
                            <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate pr-2">{{ $n->title }}</h4>
                            <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed line-clamp-3 sm:line-clamp-none">{{ $n->message }}</p>
                        
                        <div class="mt-3 flex flex-wrap items-center gap-2 sm:gap-3">
                            @if($n->user)
                                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/50 px-2 py-1 rounded-lg">
                                    <div class="size-4 sm:size-5 rounded-full bg-slate-200 bg-cover bg-center border border-white dark:border-slate-700" style="background-image: url('{{ $n->user->borrower->photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($n->user->name) }}')"></div>
                                    <span class="text-[9px] sm:text-[10px] font-bold text-slate-600 dark:text-slate-300">{{ $n->user->name }}</span>
                                </div>
                            @endif
                            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-tighter px-1.5 py-0.5 rounded bg-{{ $color }}-50 dark:bg-{{ $color }}-900/10 text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                {{ strtoupper($n->category ?? 'system') }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="size-16 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-3xl text-slate-300">notifications_off</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">No activities found</h3>
                    <p class="text-sm text-slate-500 mt-1">When system actions occur, they will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="pb-12 px-2 sm:px-0">
        {{ $notifications->links() }}
    </div>
</div>
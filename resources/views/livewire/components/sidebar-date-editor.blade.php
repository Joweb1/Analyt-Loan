<div x-data="{ open: false }" x-on:close-modal.window="open = false">
    @php
        $canEdit = Auth::user()->hasRole('Admin') || Auth::user()->can('change_system_date');
    @endphp

    @if($canEdit)
        <button @click="open = true" class="w-full text-left">
    @endif
        <div class="mb-4 px-4 py-3 bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-900/30 rounded-2xl sidebar-nav-text">
            <div class="flex items-center gap-2 text-orange-700 dark:text-orange-400 mb-1">
                <span class="material-symbols-outlined text-sm font-black">calendar_today</span>
                <span class="text-[10px] font-black uppercase tracking-widest">System Date</span>
            </div>
            <p class="text-xs font-bold text-orange-800 dark:text-orange-300">
                {{ fetch_data(\App\Models\Organization::current()?->system_date?->format('M d, Y') ?? null) }}
            </p>
        </div>
    @if($canEdit)
        </button>
    @endif

    @if($canEdit)
        <!-- Edit Modal -->
        <div x-show="open" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50" x-cloak>
            <div @click.away="open = false" class="bg-white dark:bg-zinc-800 p-6 rounded-xl shadow-xl w-full max-w-sm">
                <h3 class="text-lg font-bold mb-4 dark:text-white">Edit System Date</h3>
                <input type="date" wire:model="system_date" class="w-full p-2 mb-4 border rounded dark:bg-zinc-700 dark:border-zinc-600 dark:text-white">
                <div class="flex justify-end gap-2">
                    <button @click="open = false" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                    <button wire:click="updateDate" class="px-4 py-2 text-sm bg-primary text-white rounded">Apply</button>
                </div>
            </div>
        </div>
    @endif
</div>

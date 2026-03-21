@props(['portfolios' => [], 'portfolioId' => null])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" 
            class="bg-white dark:bg-[#1a1f2b] border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest py-2.5 px-4 focus:ring-2 focus:ring-primary/20 min-w-max flex items-center gap-2 shadow-sm transition-all hover:border-primary/30">
        <span class="material-symbols-outlined text-sm {{ $portfolioId ? 'text-primary' : 'text-slate-400' }}">folder_shared</span>
        <span>{{ $portfolioId ? (collect($portfolios)->firstWhere('id', $portfolioId)->name ?? 'By Portfolio') : 'All Portfolios' }}</span>
        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>
    
    <div x-show="open" @click.away="open = false" x-cloak
         class="absolute right-0 mt-2 w-64 bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-zinc-800 z-[100] overflow-hidden py-2 animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-4 py-2 border-b border-slate-50 dark:border-zinc-800 mb-1">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Filter by Portfolio</p>
        </div>

        <button wire:click="$set('portfolioId', null); open = false;" 
                class="w-full text-left px-4 py-3 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-800 dark:text-white flex items-center justify-between group transition-colors {{ !$portfolioId ? 'bg-primary/5 text-primary' : 'text-slate-600' }}">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-sm {{ !$portfolioId ? 'text-primary' : 'text-slate-400' }}">apps</span>
                <span>All Organization Data</span>
            </div>
            @if(!$portfolioId)
                <span class="material-symbols-outlined text-primary text-sm font-black">check</span>
            @endif
        </button>

        @foreach($portfolios as $p)
            <button wire:click="$set('portfolioId', '{{ $p->id }}'); open = false;" 
                    class="w-full text-left px-4 py-3 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-800 dark:text-white flex items-center justify-between group transition-colors {{ $portfolioId === $p->id ? 'bg-primary/5 text-primary' : 'text-slate-600' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-sm {{ $portfolioId === $p->id ? 'text-primary' : 'text-slate-400' }}">folder</span>
                    <span class="truncate max-w-[140px]">{{ $p->name }}</span>
                </div>
                @if($portfolioId === $p->id)
                    <span class="material-symbols-outlined text-primary text-sm font-black">check</span>
                @endif
            </button>
        @endforeach

        @if(empty($portfolios))
            <div class="px-4 py-6 text-center">
                <span class="material-symbols-outlined text-slate-300 text-3xl mb-2">folder_off</span>
                <p class="text-[10px] font-bold text-slate-400 uppercase">No portfolios assigned</p>
            </div>
        @endif
    </div>
</div>

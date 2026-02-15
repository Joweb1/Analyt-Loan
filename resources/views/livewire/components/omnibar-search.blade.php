<div class="relative w-full group" x-data="{ open: @entangle('query').live }">
    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <span class="material-symbols-outlined text-primary/50">search</span>
    </div>
    <input 
        wire:model.live.debounce.300ms="query" 
        @keydown.escape="open = false; $wire.set('query', '')"
        class="block w-full pl-12 pr-4 py-3.5 bg-white dark:bg-[#1a1f2b] dark:text-white border-none rounded-2xl text-sm shadow-xl focus:ring-2 focus:ring-primary/40 placeholder-slate-400 transition-all font-medium" 
        placeholder="Universal search (ID, Name, Phone, Collateral...)" 
        type="text"
    />
    
    @if(!empty($results))
        <div class="absolute mt-3 w-full bg-white dark:bg-[#1a1f2b] rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 z-[100] overflow-hidden animate-in fade-in slide-in-from-top-4 duration-200">
            <div class="p-3">
                <div class="flex items-center justify-between px-3 py-2 border-b border-slate-50 dark:border-slate-800/50 mb-2">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Search Results</span>
                    <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">{{ count($results) }} Found</span>
                </div>
                <div class="max-h-[400px] overflow-y-auto custom-scrollbar">
                    @foreach($results as $res)
                        <a href="{{ $res['link'] }}" class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all group">
                            <div class="size-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 group-hover:bg-primary group-hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-xl">{{ $res['icon'] }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-black text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $res['title'] }}</p>
                                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-tight">{{ $res['subtitle'] }}</p>
                            </div>
                            <span class="material-symbols-outlined text-slate-300 group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
                        </a>
                    @endforeach
                </div>
                <div @click="open = false; $wire.set('query', '')" class="p-3 bg-slate-50 dark:bg-slate-800/50 mt-2 rounded-2xl flex items-center justify-center cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <p class="text-[10px] font-bold text-slate-400">Press <kbd class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded text-slate-500 font-mono">ESC</kbd> or click here to close</p>
                </div>
            </div>
        </div>
    @endif

    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <kbd class="hidden sm:inline-flex items-center h-6 px-2 text-[10px] font-medium text-slate-400 bg-slate-50 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 font-mono">CTRL + K</kbd>
    </div>
</div>

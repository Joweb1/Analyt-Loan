<div class="bg-slate-900 rounded-2xl border border-white/10 shadow-2xl flex flex-col overflow-hidden transition-all duration-500" 
    x-data="{ 
        scrollBottom() { 
            this.$nextTick(() => {
                const body = document.getElementById('admin-terminal-body');
                if (body) body.scrollTop = body.scrollHeight;
            });
        }
    }"
    x-init="scrollBottom()"
    @refresh-logs.window="scrollBottom()"
    wire:poll.10s="refreshLogs">
    
    <!-- Terminal Header -->
    <div class="bg-slate-900 px-6 py-4 border-b border-white/5 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex gap-1.5">
                <div class="size-3 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]"></div>
                <div class="size-3 rounded-full bg-yellow-500 shadow-[0_0_8px_rgba(234,179,8,0.4)]"></div>
                <div class="size-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]"></div>
            </div>
            <span class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] ml-4">Analyt Platform OS / Admin Console</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1 bg-white/5 rounded-full border border-white/5">
                <div class="size-2 rounded-full bg-green-500 animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.6)]"></div>
                <span class="text-[10px] text-green-500 font-black uppercase tracking-wider">SECURE_LINK_ACTIVE</span>
            </div>
        </div>
    </div>

    <!-- Terminal Output -->
    <div id="admin-terminal-body" class="p-6 flex-1 font-mono text-[11px] overflow-y-auto terminal-scroll space-y-1.5 h-64 bg-[#050811] custom-scrollbar">
        <p class="text-gray-500 mb-4 opacity-50 tracking-tighter">*** ANALYT PLATFORM ADMINISTRATIVE INTERFACE (v2.0) ***</p>
        
        @forelse($logs as $log)
            <div class="flex gap-3 group" wire:key="admin-log-{{ $log['id'] }}">
                <span class="text-gray-600 shrink-0">[{{ \Carbon\Carbon::parse($log['created_at'])->format('H:i:s') }}]</span>
                <span class="uppercase font-black shrink-0 w-16
                    {{ $log['level'] === 'success' ? 'text-green-400' : ($log['level'] === 'error' ? 'text-red-400' : ($log['level'] === 'warning' ? 'text-yellow-400' : ($log['level'] === 'input' ? 'text-primary' : 'text-blue-400'))) }}">
                    {{ $log['level'] }}
                </span>
                <span class="text-gray-500 shrink-0 font-bold">[{{ $log['component'] }}]</span>
                <span class="text-gray-300 group-hover:text-white transition-colors">{{ $log['message'] }}</span>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center h-full opacity-30">
                <span class="material-symbols-outlined text-4xl mb-2 text-gray-500">terminal</span>
                <p class="text-xs uppercase font-black tracking-widest text-gray-500">Awaiting commands...</p>
            </div>
        @endforelse
        <div id="admin-terminal-bottom"></div>
    </div>

    <!-- Terminal Input -->
    <form wire:submit.prevent="executeCommand" class="p-4 bg-white/5 border-t border-white/5 flex items-center gap-3">
        <span class="text-primary material-symbols-outlined text-sm">terminal</span>
        <input 
            wire:model="commandInput"
            wire:loading.attr="disabled"
            class="bg-transparent border-none p-0 text-xs font-mono text-white focus:ring-0 w-full placeholder:text-gray-700 disabled:opacity-50" 
            placeholder="Type 'help' to see available commands..." 
            type="text"
            autocomplete="off"
        />
    </form>
</div>

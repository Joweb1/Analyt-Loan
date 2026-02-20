<div class="bg-primary rounded-2xl border border-white/10 shadow-2xl flex flex-col overflow-hidden transition-all duration-500" 
    x-data="{ 
        scrollBottom() { 
            this.$nextTick(() => {
                const body = document.getElementById('terminal-body');
                if (body) body.scrollTop = body.scrollHeight;
            });
        }
    }"
    x-init="scrollBottom()"
    @refresh-logs.window="scrollBottom()"
    wire:poll.5s="refreshLogs">
    
    <!-- Terminal Header -->
    <div class="bg-primary px-6 py-4 border-b border-white/5 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex gap-1.5">
                <div class="size-3 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]"></div>
                <div class="size-3 rounded-full bg-yellow-500 shadow-[0_0_8px_rgba(234,179,8,0.4)]"></div>
                <div class="size-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]"></div>
            </div>
            <span class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] ml-4">Analyt Core Kernel / Diagnostic Console</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1 bg-white/5 rounded-full border border-white/5">
                @php
                    $statusColor = match($systemStatus) {
                        'operational' => 'bg-green-500 shadow-[0_0_10px_rgba(34,197,94,0.6)]',
                        'degraded' => 'bg-yellow-500 shadow-[0_0_10px_rgba(234,179,8,0.6)]',
                        'critical' => 'bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.6)]',
                        default => 'bg-gray-500'
                    };
                    $textColor = match($systemStatus) {
                        'operational' => 'text-green-500',
                        'degraded' => 'text-yellow-500',
                        'critical' => 'text-red-500',
                        default => 'text-gray-500'
                    };
                @endphp
                <div class="size-2 rounded-full {{ $statusColor }} animate-pulse"></div>
                <span class="text-[10px] {{ $textColor }} font-black uppercase tracking-wider">
                    {{ strtoupper($systemStatus) }}
                </span>
            </div>
            <button wire:click="runDiagnostics" wire:loading.attr="disabled" class="p-1.5 text-gray-400 hover:text-white transition-colors disabled:opacity-50" title="Run Diagnostic">
                <span class="material-symbols-outlined text-[20px]" wire:loading.class="animate-spin" wire:target="runDiagnostics">refresh</span>
            </button>
        </div>
    </div>

    <!-- Terminal Output -->
    <div id="terminal-body" class="p-6 flex-1 font-mono text-[11px] overflow-y-auto terminal-scroll space-y-1.5 h-80 bg-[#050811] custom-scrollbar">
        <p class="text-gray-500 mb-4 opacity-50 tracking-tighter">*** ANALYT LOAN 2.0 SYSTEM DIAGNOSTIC INTERFACE ***</p>
        
        @forelse($logs as $log)
            <div class="flex gap-3 group" wire:key="log-{{ $log['id'] }}">
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
                <p class="text-xs uppercase font-black tracking-widest text-gray-500">No diagnostic data available</p>
            </div>
        @endforelse
        <div id="terminal-bottom"></div>
    </div>

    <!-- Terminal Input -->
    <form wire:submit.prevent="executeCommand" class="p-4 bg-white/5 border-t border-white/5 flex items-center gap-3">
        <span class="text-primary-foreground/50 material-symbols-outlined text-sm">chevron_right</span>
        <input 
            wire:model="commandInput"
            wire:loading.attr="disabled"
            class="bg-transparent border-none p-0 text-xs font-mono text-white focus:ring-0 w-full placeholder:text-gray-700 disabled:opacity-50" 
            placeholder="Type 'diagnostics' for health check, 'maintenance' for full sync..." 
            type="text"
            autocomplete="off"
        />
    </form>
</div>

<?php

use App\Models\SessionLog;
use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        return [
            'logs' => SessionLog::with('user')->latest()->limit(50)->get(),
        ];
    }
}; ?>

<div class="p-6 bg-gray-900 text-white min-h-screen font-mono">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-blue-400">SESSION_LOG_MONITOR v1.2 [FULL_DEBUG]</h1>
        <div class="text-xs text-gray-500">Refresh to update</div>
    </div>

    <div class="overflow-x-auto bg-gray-800 rounded-lg shadow-xl border border-gray-700">
        <table class="w-full text-left border-collapse table-fixed">
            <thead>
                <tr class="border-b border-gray-700 bg-gray-900/50">
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400 w-32">TIMESTAMP</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400 w-40">IDENTITY</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400 w-64">PATH_INFO</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400 w-80">SESSION_ID</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400 w-80">CSRF_TOKENS</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400">COOKIES_RAW</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-700/30 transition">
                        <td class="p-4 text-[10px] text-gray-500 whitespace-nowrap">
                            {{ fetch_data(\Carbon\Carbon::parse($log?->created_at)?->format('Y-m-d H:i:s') ?? null) }}
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col">
                                <span class="text-xs {{ fetch_data($log?->user ? 'text-green-400 font-bold' : 'text-gray-500' ?? null) }}">
                                    {{ fetch_data($log?->user?->email ?? 'ANONYMOUS' ?? null) }}
                                </span>
                                <span class="text-[9px] text-gray-600 uppercase">{{ fetch_data($log?->is_authenticated ? 'AUTH_OK' : 'GUEST' ?? null) }}</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <span class="px-1.5 py-0.5 text-[10px] bg-blue-900 text-blue-200 rounded font-bold">{{ fetch_data($log?->method ?? null) }}</span>
                                <span class="text-[10px] {{ fetch_data(Str::startsWith($log?->path, 'https') ? 'text-green-400' : 'text-red-400 font-bold' ?? null) }}">
                                    {{ fetch_data($log?->path ?? null) }}
                                </span>
                            </div>
                            <div class="text-[9px] text-gray-600 mt-1 italic">{{ fetch_data($log?->user_agent ?? null) }}</div>
                        </td>
                        <td class="p-4 font-mono text-[10px] text-yellow-500/80 break-all leading-relaxed">
                            {{ fetch_data($log?->session_id ?? null) }}
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col space-y-2">
                                <div class="text-[10px] text-gray-400 break-all leading-tight"><span class="text-gray-600 font-bold">STORE:</span> {{ fetch_data($log?->csrf_token_session ?? null) }}</div>
                                <div class="text-[10px] {{ fetch_data($log?->csrf_token_session === $log?->csrf_token_request ? 'text-green-500' : 'text-red-500 font-bold' ?? null) }} break-all leading-tight">
                                    <span class="text-gray-600 font-bold">INPUT:</span> {{ fetch_data($log?->csrf_token_request ?: 'NULL' ?? null) }}
                                </div>
                            </div>
                        </td>
                        <td class="p-4 text-[10px] text-blue-300/80 break-all font-mono leading-tight">
                            {{ fetch_data(json_encode($log?->cookies) ?? null) }}
                            <div class="mt-2 p-1 bg-gray-900 rounded border border-gray-700 text-gray-500">
                                <span class="text-[8px] font-bold uppercase">Runtime Config:</span>
                                <pre class="text-[8px] text-gray-600">{{ fetch_data(json_encode($log?->payload['runtime_config'] ?? ['status' => 'No config captured'], JSON_PRETTY_PRINT) ?? null) }}</pre>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

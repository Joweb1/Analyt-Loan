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
        <h1 class="text-2xl font-bold text-blue-400">SESSION_LOG_MONITOR v1.0</h1>
        <div class="text-xs text-gray-500">Refresh to update</div>
    </div>

    <div class="overflow-x-auto bg-gray-800 rounded-lg shadow-xl border border-gray-700">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700 bg-gray-900/50">
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400">TIMESTAMP</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400">IDENTITY</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400">REQUEST_INFO</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400">SESSION_ID</th>
                    <th class="p-4 text-xs font-bold uppercase tracking-wider text-gray-400">CSRF_VALIDATION</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-700/30 transition">
                        <td class="p-4 text-[10px] text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col">
                                <span class="text-xs {{ $log->user ? 'text-green-400 font-bold' : 'text-gray-500' }}">
                                    {{ $log->user?->email ?? 'ANONYMOUS' }}
                                </span>
                                <span class="text-[9px] text-gray-600 uppercase">{{ $log->is_authenticated ? 'AUTH_OK' : 'GUEST' }}</span>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center space-x-2">
                                <span class="px-1.5 py-0.5 text-[10px] bg-blue-900 text-blue-200 rounded font-bold">{{ $log->method }}</span>
                                <span class="text-xs text-gray-300">{{ $log->path }}</span>
                            </div>
                            <div class="text-[9px] text-gray-600 mt-1 italic">{{ Str::limit($log->user_agent, 40) }}</div>
                        </td>
                        <td class="p-4">
                            <span class="text-xs font-bold text-yellow-500/80">{{ Str::limit($log->session_id, 12) }}...</span>
                        </td>
                        <td class="p-4">
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-[9px] text-gray-500 w-8">STORE:</span>
                                    <span class="text-[10px] text-gray-400">{{ Str::limit($log->csrf_token_session, 6) }}...</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-[9px] text-gray-500 w-8">INPUT:</span>
                                    <span class="text-[10px] {{ $log->csrf_token_session === $log->csrf_token_request ? 'text-green-500' : 'text-red-500' }}">
                                        {{ $log->csrf_token_request ? Str::limit($log->csrf_token_request, 6) . '...' : 'NULL' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

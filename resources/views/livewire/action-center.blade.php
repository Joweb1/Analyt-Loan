<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
                    Action Center & Tasks
                </h2>

                <div class="space-y-4">
                    @forelse($tasks as $task)
                        <div class="flex items-center p-4 bg-white dark:bg-zinc-800 border-l-4 {{ $task['priority'] === 'critical' ? 'border-red-500' : ($task['priority'] === 'high' ? 'border-orange-500' : 'border-blue-500') }} rounded shadow-sm hover:shadow-md transition">
                            <div class="flex-shrink-0 mr-4">
                                <span class="material-symbols-outlined text-2xl {{ $task['priority'] === 'critical' ? 'text-red-500' : ($task['priority'] === 'high' ? 'text-orange-500' : 'text-blue-500') }}">
                                    {{ $task['type'] === 'overdue_loan' ? 'warning' : ($task['type'] === 'loan_approval' ? 'gavel' : ($task['type'] === 'report' ? 'bar_chart' : 'person_search')) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ $task['title'] }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $task['description'] }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $task['date']->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ $task['link'] }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-primary bg-primary/10 hover:bg-primary/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                                    Review
                                </a>
                                <button wire:click="markAsResolved('{{ $task['id'] }}')" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-600 bg-green-50 hover:bg-green-100 transition">
                                    Resolve
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">check_circle</span>
                            <p class="text-gray-500">All caught up! No pending tasks.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

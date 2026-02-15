<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex items-center space-x-6 mb-8">
                    <div class="h-24 w-24 bg-primary rounded-full flex items-center justify-center text-white text-3xl font-bold">
                        {{ substr($name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold dark:text-white">{{ $name }}</h2>
                        <p class="text-gray-500">{{ $email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded">{{ $role }}</span>
                            @if($organization !== 'None')
                                <span class="px-2 py-1 text-xs font-semibold bg-purple-100 text-purple-800 rounded">{{ $organization }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-zinc-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Actions</h3>
                    <button wire:click="logout" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-bold text-sm">
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

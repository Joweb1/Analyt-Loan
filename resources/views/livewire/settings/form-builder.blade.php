<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-6">
                    Borrower Form Customization
                </h2>
                
                <div x-data="{ activeTab: 'identity' }">
                    <div class="mb-4 border-b border-gray-200 dark:border-zinc-700">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                            @foreach($sections as $key => $label)
                                <li class="mr-2" role="presentation">
                                    <button @click="activeTab = '{{ $key }}'" :class="activeTab === '{{ $key }}' ? 'border-primary text-primary' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300'" class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-200" type="button" role="tab">{{ $label }}</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @foreach($sections as $sectionKey => $sectionLabel)
                        <div x-show="activeTab === '{{ $sectionKey }}'" class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ $sectionLabel }} Fields</h3>
                            
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                                <thead class="bg-gray-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field Label</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                                    @foreach($configs[$sectionKey] ?? [] as $field)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $field->label }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($field->type) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <button wire:click="toggleRequired('{{ $field->id }}')" class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer" {{ $field->is_required ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <button wire:click="toggleActive('{{ $field->id }}')" class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer" {{ $field->is_active ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if(!$field->is_system)
                                                    <button wire:click="deleteField('{{ $field->id }}')" class="text-red-600 hover:text-red-900 text-xs font-bold uppercase tracking-wider">Delete</button>
                                                @else
                                                    <span class="text-gray-400 text-xs italic">System Field</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Add Field Form -->
                            <div class="mt-6 p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700">
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider">Add Custom Field to {{ $sectionLabel }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Label</label>
                                        <input type="text" wire:model="newFieldLabel" class="w-full text-sm rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                                        @error('newFieldLabel') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Type</label>
                                        <select wire:model="newFieldType" class="w-full text-sm rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                                            @foreach($fieldTypes as $val => $txt)
                                                <option value="{{ $val }}">{{ $txt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div x-show="$wire.newFieldType === 'select'">
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Options (comma separated)</label>
                                        <input type="text" wire:model="newFieldOptions" placeholder="Option 1, Option 2" class="w-full text-sm rounded-md border-gray-300 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm focus:border-primary focus:ring focus:ring-primary/20">
                                    </div>
                                    <div class="flex items-end">
                                        <button wire:click="addField('{{ $sectionKey }}')" class="px-4 py-2 bg-primary text-white text-sm font-bold rounded-md hover:bg-primary/90 transition shadow-lg shadow-primary/30 w-full">
                                            Add Field
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

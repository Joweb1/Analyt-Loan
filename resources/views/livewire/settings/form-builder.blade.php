<div class="max-w-7xl mx-auto w-full">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Form Customization</h1>
        <p class="text-gray-500 mt-1">Configure and extend the borrower onboarding questionnaire.</p>
    </div>

    <x-settings-nav active="form" />

    <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow-sm rounded-2xl border border-gray-100 dark:border-zinc-800">
        <div class="p-8">
            <div x-data="{ activeTab: 'identity' }">
                <div class="mb-8 border-b border-gray-100 dark:border-zinc-800">
                    <ul class="flex flex-wrap -mb-px text-[10px] font-black uppercase tracking-widest text-center" role="tablist">
                        @foreach($sections as $key => $label)
                            <li class="mr-2" role="presentation">
                                <button @click="activeTab = '{{ $key }}'" :class="activeTab === '{{ $key }}' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-gray-600'" class="inline-block p-4 border-b-2 rounded-t-lg transition-colors duration-200" type="button" role="tab">{{ $label }}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @foreach($sections as $sectionKey => $sectionLabel)
                    <div x-show="activeTab === '{{ $sectionKey }}'" class="space-y-8 animate-in fade-in duration-300">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Field Label</th>
                                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Input Type</th>
                                        <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-slate-500">Required</th>
                                        <th class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-slate-500">Enabled</th>
                                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-slate-500">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                                    @foreach($configs[$sectionKey] ?? [] as $field)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-zinc-800/20 transition-colors">
                                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $field->label }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 rounded bg-slate-100 dark:bg-zinc-800 text-[10px] font-black uppercase text-slate-500">{{ $field->type }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button wire:click="toggleRequired('{{ $field->id }}')" class="relative inline-flex items-center cursor-pointer">
                                                    <div class="w-10 h-5 bg-gray-200 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all {{ $field->is_required ? 'bg-primary' : '' }}">
                                                        <div class="size-4 bg-white rounded-full transition-all mt-[2px] ml-[2px] {{ $field->is_required ? 'translate-x-5' : '' }}"></div>
                                                    </div>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button wire:click="toggleActive('{{ $field->id }}')" class="relative inline-flex items-center cursor-pointer">
                                                    <div class="w-10 h-5 bg-gray-200 rounded-full peer dark:bg-zinc-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all {{ $field->is_active ? 'bg-primary' : '' }}">
                                                        <div class="size-4 bg-white rounded-full transition-all mt-[2px] ml-[2px] {{ $field->is_active ? 'translate-x-5' : '' }}"></div>
                                                    </div>
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if(!$field->is_system)
                                                    <button wire:click="deleteField('{{ $field->id }}')" class="text-red-500 hover:text-red-700 text-[10px] font-black uppercase tracking-widest">Delete</button>
                                                @else
                                                    <span class="text-slate-300 text-[10px] font-black uppercase">System</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Add Field Form -->
                        <div class="p-8 bg-slate-50 dark:bg-zinc-800/30 rounded-[2rem] border border-slate-100 dark:border-zinc-800">
                            <h4 class="text-xs font-black text-primary dark:text-white mb-6 uppercase tracking-[0.2em]">Add Custom Field to {{ $sectionLabel }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Field Label</label>
                                    <input type="text" wire:model="newFieldLabel" class="w-full rounded-xl border-gray-200 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white focus:ring-primary py-3 px-4 font-bold text-sm" placeholder="e.g. Spouse Name">
                                    @error('newFieldLabel') <span class="text-red-500 text-[10px] font-bold mt-1 block px-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Input Type</label>
                                    <select wire:model="newFieldType" class="w-full rounded-xl border-gray-200 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white focus:ring-primary py-3 px-4 font-bold text-sm">
                                        @foreach($fieldTypes as $val => $txt)
                                            <option value="{{ $val }}">{{ $txt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-1" x-show="$wire.newFieldType === 'select'">
                                    <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2 px-1">Options (Comma separated)</label>
                                    <input type="text" wire:model="newFieldOptions" placeholder="Option 1, Option 2" class="w-full rounded-xl border-gray-200 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white focus:ring-primary py-3 px-4 font-bold text-sm">
                                </div>
                                <div class="flex items-end">
                                    <button wire:click="addField('{{ $sectionKey }}')" class="w-full bg-primary text-white py-3.5 rounded-xl font-black uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all">
                                        Add Custom Field
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

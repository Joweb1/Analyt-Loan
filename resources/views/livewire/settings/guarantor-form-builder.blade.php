<div class="max-w-7xl mx-auto w-full">
    <div class="mb-8">
        <h1 class="text-primary dark:text-white text-3xl font-extrabold tracking-tight">Guarantor Form Customization</h1>
        <p class="text-gray-500 mt-1">Configure exactly what data you want to collect from guarantors.</p>
    </div>

    <x-settings-nav active="guarantor-form" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Schema Builder (Left 2 columns) -->
        <div class="lg:col-span-2 space-y-6">
            @foreach($sections as $sectionKey => $sectionLabel)
                <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $sectionLabel }}</h3>
                        <button wire:click="$set('newFieldSection', '{{ $sectionKey }}')" class="text-xs font-bold text-primary hover:underline">
                            + Add Field Here
                        </button>
                    </div>

                    <div class="space-y-3">
                        @if(isset($configs[$sectionKey]))
                            @foreach($configs[$sectionKey] as $field)
                                <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                                    <div class="flex items-center gap-3">
                                        <div class="cursor-move text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                            <span class="material-symbols-outlined text-sm">drag_indicator</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-900 dark:text-white">
                                                {{ $field->label }}
                                                @if($field->is_system)
                                                    <span class="ml-2 text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">System</span>
                                                @endif
                                            </p>
                                            <p class="text-[10px] text-slate-500 uppercase tracking-widest">{{ $fieldTypes[$field->type] ?? $field->type }} | Name: {{ $field->name }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Required</span>
                                            <input type="checkbox" wire:change="toggleRequired({{ $field->id }})" @if($field->is_required) checked @endif class="rounded border-slate-300 text-primary focus:ring-primary">
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Active</span>
                                            <input type="checkbox" wire:change="toggleActive({{ $field->id }})" @if($field->is_active) checked @endif class="rounded border-slate-300 text-green-600 focus:ring-green-600">
                                        </label>
                                        @if(!$field->is_system)
                                            <button wire:click="deleteField({{ $field->id }})" class="text-red-500 hover:text-red-700 transition-colors p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        @else
                                            <div class="w-6"></div> <!-- Placeholder for alignment -->
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-slate-500 italic">No fields in this section yet.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Add Field Form (Right Column) -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Add Custom Field</h3>
                <form wire:submit.prevent="addField" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Section</label>
                        <select wire:model="newFieldSection" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium text-slate-700 dark:text-slate-300">
                            @foreach($sections as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('newFieldSection') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Field Label (UI Name)</label>
                        <input wire:model="newFieldLabel" type="text" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium text-slate-900 dark:text-white placeholder-slate-400" placeholder="e.g., Mother's Maiden Name">
                        @error('newFieldLabel') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Field Type</label>
                        <select wire:model="newFieldType" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium text-slate-700 dark:text-slate-300">
                            @foreach($fieldTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('newFieldType') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    @if($newFieldType === 'select')
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Options (Comma Separated)</label>
                            <input wire:model="newFieldOptions" type="text" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium text-slate-900 dark:text-white placeholder-slate-400" placeholder="e.g., Yes, No, Maybe">
                        </div>
                    @endif

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-primary text-white text-sm font-bold rounded-xl shadow-md shadow-primary/20 hover:bg-primary/90 hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            Add Field
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

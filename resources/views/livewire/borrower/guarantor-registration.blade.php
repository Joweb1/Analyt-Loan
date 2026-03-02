<div class="max-w-4xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Register Guarantor</h1>
        <p class="mt-2 text-sm text-slate-500">Collect and store guarantor details for future reference.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-8">
        @if(empty($configs))
            <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl text-sm font-medium">
                Loading configuration or no fields configured.
            </div>
        @else
            @foreach($configs as $sectionKey => $fields)
                <div class="bg-white dark:bg-[#1a1f2b] rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-8">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6 capitalize">{{ str_replace('_', ' ', $sectionKey) }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($fields as $field)
                            @php
                                $modelBinding = $field['is_system'] ? $field['name'] : 'customData.'.$field['name'];
                            @endphp
                            <div class="{{ in_array($field['type'], ['textarea', 'file']) ? 'md:col-span-2' : '' }}">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                    {{ $field['label'] }}
                                    @if($field['is_required']) <span class="text-red-500">*</span> @endif
                                </label>

                                @if($field['type'] === 'textarea')
                                    <textarea wire:model="{{ $modelBinding }}" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium"></textarea>
                                @elseif($field['type'] === 'select')
                                    <select wire:model="{{ $modelBinding }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium text-slate-700 dark:text-slate-300">
                                        <option value="">Select an option</option>
                                        @if($field['options'])
                                            @foreach($field['options'] as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @elseif($field['type'] === 'file')
                                    <input type="file" wire:model="{{ $modelBinding }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                                    <div wire:loading wire:target="{{ $modelBinding }}" class="text-xs text-primary mt-1 font-medium">Uploading...</div>
                                @else
                                    <input type="{{ $field['type'] }}" wire:model="{{ $modelBinding }}" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/40 font-medium placeholder-slate-400">
                                @endif

                                @error($modelBinding) <span class="text-xs font-bold text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <div class="flex justify-end gap-4">
            <button type="button" onclick="window.history.back()" class="px-6 py-3 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button type="submit" class="px-8 py-3 text-sm font-bold text-white bg-primary rounded-xl shadow-lg shadow-primary/30 hover:bg-primary/90 hover:-translate-y-0.5 transition-all">
                Register Guarantor
            </button>
        </div>
    </form>
</div>

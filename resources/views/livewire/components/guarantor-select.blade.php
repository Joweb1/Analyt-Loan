<div class="relative w-full" x-data="{ open: false }" @click.outside="open = false">
    @if($selectedGuarantor)
        <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-xl">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                    <span class="material-symbols-outlined text-xl">{{ $selectedGuarantor['type'] === 'internal' ? 'person' : 'shield_person' }}</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $selectedGuarantor['name'] }}</p>
                    <p class="text-[10px] text-blue-700 uppercase font-bold tracking-wider">{{ $selectedGuarantor['type'] === 'internal' ? 'Customer' : 'External Guarantor' }}</p>
                </div>
            </div>
            <button type="button" wire:click="clearSelection" class="p-1 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @else
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-gray-400 text-[20px]">search</span>
            </div>
            <input 
                wire:model.live.debounce.300ms="search" 
                @focus="open = true"
                type="text" 
                placeholder="Search Name, Phone, or ID..." 
                class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-700 focus:border-blue-700 sm:text-sm transition-all"
                autocomplete="off"
            >
        </div>

        @if(count($results) > 0)
            <div x-show="open" class="absolute z-[100] mt-1 w-full bg-white shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                @foreach($results as $res)
                    <div wire:click="selectGuarantor('{{ $res['id'] }}', '{{ $res['type'] }}', '{{ $res['name'] }}')" @click="open = false" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-blue-50 transition-colors border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <span class="material-symbols-outlined text-sm">{{ $res['type'] === 'internal' ? 'person' : 'shield_person' }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $res['name'] }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] text-gray-500">{{ $res['subtitle'] }}</span>
                                    @if($res['custom_id'])
                                        <span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded font-mono">{{ $res['custom_id'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif(strlen($search) >= 2)
            <div x-show="open" class="absolute z-[100] mt-1 w-full bg-white shadow-lg rounded-xl py-4 text-center text-sm text-gray-500 border border-gray-100">
                <p>No customers or guarantors found.</p>
                <a href="{{ route('guarantor.create') }}" target="_blank" class="mt-2 inline-block text-[10px] font-bold text-blue-700 hover:underline">
                    + Register New Guarantor
                </a>
            </div>
        @endif
    @endif
</div>

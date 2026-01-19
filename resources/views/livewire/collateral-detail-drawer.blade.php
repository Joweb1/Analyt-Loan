<div x-data="{ isOpen: @entangle('isOpen') }">
    <div x-show="isOpen" class="fixed inset-0 overflow-hidden z-50">
        <div class="absolute inset-0 overflow-hidden">
            <div x-show="isOpen" x-transition.opacity class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="isOpen" x-transition.duration.500ms class="w-screen max-w-md">
                    <div class="h-full flex flex-col py-6 bg-white shadow-xl overflow-y-scroll">
                        <div class="px-4 sm:px-6">
                            <h2 class="text-lg font-medium text-gray-900">
                                Collateral Details
                            </h2>
                            <div class="mt-3 flex items-center justify-end">
                                <button type="button" class="text-gray-400 hover:text-gray-500" wire:click="closeDrawer">
                                    <span class="sr-only">Close panel</span>
                                    <svg class="h-6 w-6" x-description="Heroicon name: outline/x" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="mt-6 relative flex-1 px-4 sm:px-6">
                            @if ($collateral)
                                <h3 class="text-xl font-bold">{{ $collateral->name }}</h3>
                                <p class="text-gray-600">{{ $collateral->description }}</p>
                                <p class="text-gray-800 font-bold">Value: ${{ number_format($collateral->value, 2) }}</p>
                                @if ($collateral->image_path)
                                    <img src="{{ $collateral->image_path }}" alt="{{ $collateral->name }}" class="mt-4 rounded-md">
                                @endif
                                <p class="text-gray-800 mt-2">Status: {{ $collateral->status }}</p>
                                <p class="text-gray-800">Loan ID: {{ $collateral->loan_id }}</p>
                            @else
                                <p>No collateral selected.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div>
    <form wire:submit.prevent="saveLoan" class="space-y-4">
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        <div>
            <label for="borrowerId" class="block text-sm font-medium text-gray-700">Borrower</label>
            <select wire:model="borrowerId" id="borrowerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select a Borrower</option>
                @foreach ($borrowers as $borrower)
                    <option value="{{ $borrower->id }}">{{ $borrower->phone }}</option>
                @endforeach
            </select>
            @error('borrowerId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">Loan Amount</label>
            <input type="number" wire:model.live="amount" id="amount" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="collateralId" class="block text-sm font-medium text-gray-700">Collateral</label>
            <select wire:model.live="collateralId" id="collateralId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select Collateral</option>
                @foreach ($collaterals as $collateral)
                    <option value="{{ $collateral->id }}">{{ $collateral->name }} (${{ number_format($collateral->value, 2) }})</option>
                @endforeach
            </select>
            @error('collateralId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Create Loan
        </button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($collaterals as $collateral)
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="text-lg font-semibold">{{ $collateral->name }}</h3>
            <p class="text-gray-600">{{ $collateral->description }}</p>
            <p class="text-gray-800 font-bold">${{ number_format($collateral->value, 2) }}</p>
            @if ($collateral->image_path)
                <img src="{{ $collateral->image_path }}" alt="{{ $collateral->name }}" class="mt-2 rounded-md">
            @endif
        </div>
    @endforeach
</div>

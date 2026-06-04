<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($collaterals as $collateral)
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="text-lg font-semibold">{{ fetch_data($collateral?->name ?? null) }}</h3>
            <p class="text-gray-600">{{ fetch_data($collateral?->description ?? null) }}</p>
            <p class="text-gray-800 font-bold">${{ fetch_data($collateral?->value?->format() ?? null) }}</p>
            @if ($collateral->image_path)
                <img src="{{ fetch_data($collateral?->image_path ?? null) }}" alt="{{ fetch_data($collateral?->name ?? null) }}" class="mt-2 rounded-md">
            @endif
        </div>
    @endforeach
</div>

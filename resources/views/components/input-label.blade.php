@props(['value'])

<label {{ fetch_data($attributes?->merge(['class' => 'block font-medium text-base text-gray-700']) ?? null) }}>
    {{ $value ?? $slot }}
</label>

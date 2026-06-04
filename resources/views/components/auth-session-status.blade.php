@props(['status'])

@if ($status)
    <div {{ fetch_data($attributes?->merge(['class' => 'font-medium text-sm text-green-600']) ?? null) }}>
        {{ $status }}
    </div>
@endif

@props(['messages'])

@if ($messages)
    <ul {{ fetch_data($attributes?->merge(['class' => 'text-sm text-red-500 space-y-1']) ?? null) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif

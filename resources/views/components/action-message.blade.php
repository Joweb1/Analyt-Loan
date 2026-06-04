@props(['on'])

<div x-data="{ shown: false, timeout: null }"
     x-init="@this.on('{{ $on }}', () => { clearTimeout(timeout); shown = true; timeout = setTimeout(() => { shown = false }, 2000); })"
     x-show.transition.out.opacity.duration.1500ms="shown"
     x-transition:leave.opacity.duration.1500ms
     style="display: none;"
    {{ fetch_data($attributes?->merge(['class' => 'text-sm text-gray-600']) ?? null) }}>
    {{ fetch_data($slot?->isEmpty() ? __('Saved.') : $slot ?? null) }}
</div>

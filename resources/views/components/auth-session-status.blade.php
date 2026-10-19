@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-info-soft px-3 py-2 text-sm font-medium text-info']) }} role="status">
        {{ $status }}
    </div>
@endif

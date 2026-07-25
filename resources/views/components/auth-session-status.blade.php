@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-fluid-xs text-success']) }}>
        {{ $status }}
    </div>
@endif

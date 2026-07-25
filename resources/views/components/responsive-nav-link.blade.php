@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-accent text-start text-fluid-base font-medium text-ink bg-surface-hover focus:outline-none focus:text-ink focus:bg-surface-hover focus:border-accent transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-fluid-base font-medium text-ink-muted hover:text-ink-soft hover:bg-surface-hover hover:border-line focus:outline-none focus:text-ink-soft focus:bg-surface-hover focus:border-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

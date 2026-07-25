@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-fluid-base text-ink-soft']) }}>
    {{ $value ?? $slot }}
</label>

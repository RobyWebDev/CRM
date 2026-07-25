@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong rounded-md shadow-sm']) }}>

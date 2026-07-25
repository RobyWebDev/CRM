<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-surface border border-line-strong rounded-md font-semibold text-fluid-xs text-ink-soft uppercase tracking-widest shadow-sm hover:bg-surface-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-line-strong focus-visible:ring-offset-2 focus-visible:ring-offset-page disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

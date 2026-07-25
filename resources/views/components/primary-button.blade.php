<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-accent border border-transparent rounded-md font-semibold text-fluid-xs text-accent-ink uppercase tracking-widest hover:opacity-90 focus:outline-none focus-visible:ring-2 focus-visible:ring-line-strong focus-visible:ring-offset-2 focus-visible:ring-offset-page active:opacity-80 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

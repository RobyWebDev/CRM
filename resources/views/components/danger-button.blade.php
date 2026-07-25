<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-danger border border-transparent rounded-md font-semibold text-fluid-xs text-page uppercase tracking-widest hover:opacity-90 active:opacity-80 focus:outline-none focus-visible:ring-2 focus-visible:ring-danger focus-visible:ring-offset-2 focus-visible:ring-offset-page transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>

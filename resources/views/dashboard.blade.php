<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <div class="bg-surface border border-line overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-fluid-md text-ink">
                    <p class="text-fluid-lg font-semibold">
                        {{ __('Üdv, :name!', ['name' => Auth::user()->name]) }}
                    </p>
                    <p class="text-ink-soft mt-1">
                        {{ __('Ez a :account fiók vezérlőpultja.', ['account' => Auth::user()->account?->name ?? '—']) }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-fluid-sm">
                <a href="{{ route('contacts.index') }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-md transition">
                    <p class="text-fluid-xs text-ink-muted uppercase tracking-wide">{{ __('Kontaktok') }}</p>
                    <p class="text-fluid-2xl font-semibold text-ink mt-1">{{ $contactsCount ?? 0 }}</p>
                </a>
                <a href="{{ route('deals.index') }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-md transition">
                    <p class="text-fluid-xs text-ink-muted uppercase tracking-wide">{{ __('Nyitott dealek') }}</p>
                    <p class="text-fluid-2xl font-semibold text-ink mt-1">{{ $openDealsCount ?? 0 }}</p>
                </a>
                <div class="bg-surface border border-line rounded-lg p-fluid-md">
                    <p class="text-fluid-xs text-ink-muted uppercase tracking-wide">{{ __('Aktív projektek') }}</p>
                    <p class="text-fluid-2xl font-semibold text-ink mt-1">—</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Szervezetek') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'organization-updated') {{ __('Szervezet frissítve.') }} @endif
                    @if (session('status') === 'organization-deleted') {{ __('Szervezet törölve.') }} @endif
                </div>
            @endif

            <p class="text-ink-muted text-fluid-xs">
                {{ __('Új szervezet a kontakt felvételi/szerkesztő űrlapján, a "+ Új szervezet..." opcióval hozható létre.') }}
            </p>

            <form method="GET" class="flex gap-fluid-xs">
                <x-text-input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Keresés név alapján...') }}" class="block w-full" />
                <x-secondary-button type="submit">{{ __('Keresés') }}</x-secondary-button>
            </form>

            @if ($organizations->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Még nincs egy szervezet sem.') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($organizations as $organization)
                        <a href="{{ route('organizations.show', $organization) }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-sm transition">
                            <p class="font-semibold text-ink text-fluid-base">{{ $organization->name }}</p>
                            @if ($organization->industry)
                                <p class="text-ink-muted text-fluid-xs">{{ $organization->industry }}</p>
                            @endif
                            <p class="text-ink-soft text-fluid-xs mt-1">{{ $organization->contacts_count }} {{ __('kontakt') }}</p>
                        </a>
                    @endforeach
                </div>

                <div>{{ $organizations->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

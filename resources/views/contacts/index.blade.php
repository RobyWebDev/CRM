<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Kontaktok') }}
            </h2>
            <a href="{{ route('contacts.create') }}">
                <x-primary-button>{{ __('+ Új kontakt') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'contact-created') {{ __('Kontakt létrehozva.') }} @endif
                    @if (session('status') === 'contact-updated') {{ __('Kontakt frissítve.') }} @endif
                    @if (session('status') === 'contact-deleted') {{ __('Kontakt törölve.') }} @endif
                </div>
            @endif

            <form method="GET" class="flex gap-fluid-xs">
                <x-text-input type="search" name="q" value="{{ $q }}" placeholder="{{ __('Keresés név vagy e-mail alapján...') }}" class="block w-full" />
                <x-secondary-button type="submit">{{ __('Keresés') }}</x-secondary-button>
            </form>

            @if ($contacts->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Még nincs egy kontakt sem.') }}
                    <a href="{{ route('contacts.create') }}" class="text-accent underline">{{ __('Vedd fel az elsőt.') }}</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($contacts as $contact)
                        <a href="{{ route('contacts.show', $contact) }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-sm transition">
                            <p class="font-semibold text-ink text-fluid-base">
                                {{ $contact->full_name }}
                            </p>
                            @if ($contact->organization)
                                <p class="text-ink-muted text-fluid-xs">{{ $contact->organization->name }}</p>
                            @endif
                            @if ($contact->email)
                                <p class="text-ink-soft text-fluid-xs mt-1">{{ $contact->email }}</p>
                            @endif
                            @if ($contact->phone)
                                <p class="text-ink-soft text-fluid-xs">{{ $contact->phone }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>

                <div>
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

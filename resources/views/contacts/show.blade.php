<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ trim($contact->first_name.' '.$contact->last_name) }}
            </h2>
            <div class="flex gap-fluid-xs">
                <a href="{{ route('contacts.edit', $contact) }}"><x-secondary-button type="button">{{ __('Szerkesztés') }}</x-secondary-button></a>
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                    @csrf
                    @method('delete')
                    <x-danger-button>{{ __('Törlés') }}</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                @if ($contact->organization)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Szervezet') }}:</span> <span class="text-ink">{{ $contact->organization->name }}</span></p>
                @endif
                @if ($contact->email)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('E-mail') }}:</span> <span class="text-ink">{{ $contact->email }}</span></p>
                @endif
                @if ($contact->phone)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Telefon') }}:</span> <span class="text-ink">{{ $contact->phone }}</span></p>
                @endif
                @if ($contact->source)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Forrás') }}:</span> <span class="text-ink">{{ $contact->source }}</span></p>
                @endif
            </div>

            <a href="{{ route('contacts.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a listához') }}</a>
        </div>
    </div>
</x-app-layout>

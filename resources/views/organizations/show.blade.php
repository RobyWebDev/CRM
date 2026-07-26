<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ $organization->name }}
            </h2>
            <div class="flex gap-fluid-xs">
                <a href="{{ route('organizations.edit', $organization) }}"><x-secondary-button type="button">{{ __('Szerkesztés') }}</x-secondary-button></a>
                <form method="POST" action="{{ route('organizations.destroy', $organization) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                    @csrf
                    @method('delete')
                    <x-danger-button>{{ __('Törlés') }}</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status') === 'organization-updated')
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">{{ __('Szervezet frissítve.') }}</div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                @if ($organization->industry)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Iparág') }}:</span> <span class="text-ink">{{ $organization->industry }}</span></p>
                @endif
                @if ($organization->website)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Weboldal') }}:</span> <a href="{{ $organization->website }}" target="_blank" rel="noopener" class="text-accent underline">{{ $organization->website }}</a></p>
                @endif
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Kontaktok') }}</h3>
                @if ($organization->contacts->isEmpty())
                    <p class="text-ink-muted text-fluid-xs">{{ __('Még nincs kontakt ehhez a szervezethez rendelve.') }}</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($organization->contacts as $contact)
                            <li class="py-2">
                                <a href="{{ route('contacts.show', $contact) }}" class="text-accent underline text-fluid-sm">{{ $contact->full_name }}</a>
                                @if ($contact->job_title)
                                    <span class="text-ink-muted text-fluid-xs">— {{ $contact->job_title }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <a href="{{ route('organizations.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a listához') }}</a>
        </div>
    </div>
</x-app-layout>

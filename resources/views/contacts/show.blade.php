<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <div>
                @if ($contact->organization)
                    <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                        {{ $contact->organization->name }}
                    </h2>
                    <p class="text-ink-soft text-fluid-base leading-tight">{{ $contact->full_name }}</p>
                @else
                    <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                        {{ $contact->full_name }}
                    </h2>
                @endif
            </div>
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
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'contact-created') {{ __('Kontakt létrehozva.') }} @endif
                    @if (session('status') === 'contact-updated') {{ __('Kontakt frissítve.') }} @endif
                    @if (session('status') === 'task-created') {{ __('Teendő felvéve.') }} @endif
                    @if (session('status') === 'task-recurred') {{ __('Teendő kész, a következő előfordulás automatikusan létrejött.') }} @endif
                </div>
            @endif

            @if (session('duplicate_contacts') && count(session('duplicate_contacts')) > 0)
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-warning">
                    <p class="font-medium">{{ __('Figyelem: hasonló e-maillel/telefonnal már létezik kontakt:') }}</p>
                    <ul class="list-disc list-inside mt-1">
                        @foreach (session('duplicate_contacts') as $duplicate)
                            <li><a href="{{ route('contacts.show', $duplicate['id']) }}" class="underline">{{ $duplicate['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Elérhetőségek') }}</h3>
                @if ($contact->email)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('E-mail') }}:</span> <span class="text-ink">{{ $contact->email }}</span></p>
                @endif
                @if ($contact->phone)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Telefon') }}:</span> <span class="text-ink">{{ $contact->phone }}</span></p>
                @endif
                @if ($contact->website)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Weboldal') }}:</span> <a href="{{ $contact->website }}" target="_blank" rel="noopener" class="text-accent underline">{{ $contact->website }}</a></p>
                @endif
                @if ($contact->address)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Cím') }}:</span> <span class="text-ink whitespace-pre-line">{{ $contact->address }}</span></p>
                @endif
                @foreach ($contact->contactFieldsWithDisplayLabels() as $field)
                    <p><span class="text-ink-muted text-fluid-xs">{{ $field->label }}:</span> <span class="text-ink whitespace-pre-line">{{ $field->value }}</span></p>
                @endforeach
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                @if ($contact->job_title)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Beosztás') }}:</span> <span class="text-ink">{{ $contact->job_title }}</span></p>
                @endif
                @if ($contact->organization)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Szervezet') }}:</span> <span class="text-ink">{{ $contact->organization->name }}</span></p>
                @endif
                @if ($contact->birthday)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Születésnap') }}:</span> <span class="text-ink">{{ $contact->birthday->format('Y.m.d.') }}</span></p>
                @endif
                @if ($contact->source)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Forrás') }}:</span> <span class="text-ink">{{ $contact->source }}</span></p>
                @endif
                @if ($contact->referredBy)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Ki ajánlotta') }}:</span> <a href="{{ route('contacts.show', $contact->referredBy) }}" class="text-accent underline">{{ $contact->referredBy->full_name }}</a></p>
                @endif
                @if ($contact->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-1 pt-1">
                        @foreach ($contact->tags as $tag)
                            <a href="{{ route('contacts.index', ['tag' => $tag->name]) }}" class="text-fluid-xs px-2 py-0.5 rounded bg-sunken text-ink-soft hover:bg-surface-hover">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                @endif
                @if ($contact->referrals->isNotEmpty())
                    <div class="pt-1">
                        <span class="text-ink-muted text-fluid-xs">{{ __('Ő ajánlotta') }}:</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach ($contact->referrals as $referral)
                                <a href="{{ route('contacts.show', $referral) }}" class="text-fluid-xs px-2 py-0.5 rounded bg-sunken text-ink-soft hover:bg-surface-hover">{{ $referral->full_name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Teendők') }}</h3>
                <x-task-list :taskable="$contact" taskable-type="contact" />
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Jegyzetek') }}</h3>
                <x-note-list :noteable="$contact" noteable-type="contact" />
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Aktivitás') }}</h3>
                <x-activity-timeline :subject="$contact" />
            </div>

            <a href="{{ route('contacts.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a listához') }}</a>
        </div>
    </div>
</x-app-layout>

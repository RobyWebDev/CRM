<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Keresés') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <form method="GET" action="{{ route('search') }}" class="flex gap-2">
                <label class="sr-only" for="q">{{ __('Keresés') }}</label>
                <input type="text" id="q" name="q" value="{{ $q }}" autofocus
                       placeholder="{{ __('Keresés kontaktok, leadek, üzletek, projektek, retainerek között…') }}"
                       class="flex-1 rounded-md border-line-strong bg-surface text-ink focus:border-line-strong focus:ring-line-strong">
                <x-primary-button type="submit">{{ __('Keresés') }}</x-primary-button>
            </form>

            @if ($q === '')
                <p class="text-ink-muted text-fluid-sm">{{ __('Írj be egy keresőszót fent.') }}</p>
            @else
                @php
                    $totalCount = collect($results)->sum(fn ($group) => $group->count());
                @endphp

                @if ($totalCount === 0)
                    <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                        {{ __('Nincs találat erre: ":q"', ['q' => $q]) }}
                    </div>
                @else
                    @if ($results['contacts']->isNotEmpty())
                        <div class="bg-surface border border-line rounded-lg p-fluid-md">
                            <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Kontaktok') }}</h3>
                            <div class="space-y-1">
                                @foreach ($results['contacts'] as $contact)
                                    <a href="{{ route('contacts.show', $contact) }}" class="block px-3 py-2 rounded-md hover:bg-surface-hover">
                                        <span class="text-ink font-medium">{{ $contact->full_name }}</span>
                                        @if ($contact->email)<span class="text-ink-muted text-fluid-xs ms-2">{{ $contact->email }}</span>@endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($results['organizations']->isNotEmpty())
                        <div class="bg-surface border border-line rounded-lg p-fluid-md">
                            <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Cégek') }}</h3>
                            <div class="space-y-1">
                                @foreach ($results['organizations'] as $organization)
                                    <div class="px-3 py-2 rounded-md">
                                        <span class="text-ink font-medium">{{ $organization->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($results['leads']->isNotEmpty())
                        <div class="bg-surface border border-line rounded-lg p-fluid-md">
                            <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Leadek') }}</h3>
                            <div class="space-y-1">
                                @foreach ($results['leads'] as $lead)
                                    <a href="{{ route('leads.edit', $lead) }}" class="block px-3 py-2 rounded-md hover:bg-surface-hover">
                                        <span class="text-ink font-medium">{{ $lead->full_name }}</span>
                                        @if ($lead->company)<span class="text-ink-muted text-fluid-xs ms-2">{{ $lead->company }}</span>@endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($results['deals']->isNotEmpty())
                        <div class="bg-surface border border-line rounded-lg p-fluid-md">
                            <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Üzletek') }}</h3>
                            <div class="space-y-1">
                                @foreach ($results['deals'] as $deal)
                                    <a href="{{ route('deals.edit', $deal) }}" class="block px-3 py-2 rounded-md hover:bg-surface-hover">
                                        <span class="text-ink font-medium">{{ $deal->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($results['projects']->isNotEmpty())
                        <div class="bg-surface border border-line rounded-lg p-fluid-md">
                            <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Projektek') }}</h3>
                            <div class="space-y-1">
                                @foreach ($results['projects'] as $project)
                                    <a href="{{ route('projects.show', $project) }}" class="block px-3 py-2 rounded-md hover:bg-surface-hover">
                                        <span class="text-ink font-medium">{{ $project->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($results['retainers']->isNotEmpty())
                        <div class="bg-surface border border-line rounded-lg p-fluid-md">
                            <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Retainerek') }}</h3>
                            <div class="space-y-1">
                                @foreach ($results['retainers'] as $retainer)
                                    <a href="{{ route('retainers.show', $retainer) }}" class="block px-3 py-2 rounded-md hover:bg-surface-hover">
                                        <span class="text-ink font-medium">{{ $retainer->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
</x-app-layout>

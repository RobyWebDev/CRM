<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ $project->title }}
            </h2>
            <div class="flex gap-fluid-xs">
                <a href="{{ route('projects.edit', $project) }}"><x-secondary-button type="button">{{ __('Szerkesztés') }}</x-secondary-button></a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
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
                    @if (session('status') === 'project-updated') {{ __('Projekt frissítve.') }} @endif
                    @if (session('status') === 'task-created') {{ __('Teendő felvéve.') }} @endif
                </div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                @if ($project->deal)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Eredeti üzlet') }}:</span> <a href="{{ route('deals.edit', $project->deal) }}" class="text-accent underline">{{ $project->deal->title }}</a></p>
                @endif
                @if ($project->contact)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Kontakt') }}:</span> <a href="{{ route('contacts.show', $project->contact) }}" class="text-accent underline">{{ $project->contact->full_name }}</a></p>
                @endif
                @if ($project->serviceType)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Szolgáltatás') }}:</span> <span class="text-ink">{{ $project->serviceType->name }}</span></p>
                @endif
                @if ($project->budget)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Büdzsé') }}:</span> <span class="text-ink">{{ number_format($project->budget, 0, ',', ' ') }} Ft</span></p>
                @endif
                @if ($project->start_date || $project->due_date)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Időszak') }}:</span> <span class="text-ink">{{ $project->start_date?->format('Y.m.d.') }} @if($project->due_date) – {{ $project->due_date->format('Y.m.d.') }} @endif</span></p>
                @endif
                <p><span class="text-ink-muted text-fluid-xs">{{ __('Állapot') }}:</span> <span class="text-ink font-semibold">
                    {{ __(match ($project->status) {
                        'active' => 'Aktív',
                        'on_hold' => 'Szüneteltetve',
                        'completed' => 'Befejezve',
                        'cancelled' => 'Lemondva',
                        default => $project->status,
                    }) }}
                </span></p>
                @if ($project->description)
                    <p class="text-ink-soft whitespace-pre-line">{{ $project->description }}</p>
                @endif
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Teendők') }}</h3>
                <x-task-list :taskable="$project" taskable-type="project" />
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Jegyzetek') }}</h3>
                <x-note-list :noteable="$project" noteable-type="project" />
            </div>

            <a href="{{ route('projects.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a listához') }}</a>
        </div>
    </div>
</x-app-layout>

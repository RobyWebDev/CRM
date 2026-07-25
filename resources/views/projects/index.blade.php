<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Projektek') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'project-updated') {{ __('Projekt frissítve.') }} @endif
                    @if (session('status') === 'project-deleted') {{ __('Projekt törölve.') }} @endif
                </div>
            @endif

            <div class="flex gap-fluid-xs flex-wrap">
                @foreach (['' => 'Aktív', 'all' => 'Összes', 'on_hold' => 'Szüneteltetve', 'completed' => 'Befejezve', 'cancelled' => 'Lemondva'] as $value => $label)
                    <a href="{{ route('projects.index', array_filter(['status' => $value])) }}"
                       class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $status === $value ? 'bg-accent text-accent-ink' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            @if ($projects->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Nincs ilyen állapotú projekt. Egy deal "nyert" lépésre mozgatásakor automatikusan létrejön ide egy projekt (ha a pipeline erre van beállítva).') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-sm transition">
                            <p class="font-semibold text-ink text-fluid-base">{{ $project->title }}</p>
                            @if ($project->contact)
                                <p class="text-ink-muted text-fluid-xs mt-1">{{ $project->contact->full_name }}</p>
                            @endif
                            @if ($project->serviceType)
                                <p class="text-ink-soft text-fluid-xs">{{ $project->serviceType->name }}</p>
                            @endif
                            @if ($project->budget)
                                <p class="text-ink-soft text-fluid-xs">{{ number_format($project->budget, 0, ',', ' ') }} Ft</p>
                            @endif
                            <p class="text-fluid-xs mt-2 inline-block px-2 py-0.5 rounded bg-sunken text-ink-soft">
                                {{ __(match ($project->status) {
                                    'active' => 'Aktív',
                                    'on_hold' => 'Szüneteltetve',
                                    'completed' => 'Befejezve',
                                    'cancelled' => 'Lemondva',
                                    default => $project->status,
                                }) }}
                            </p>
                        </a>
                    @endforeach
                </div>

                <div>{{ $projects->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

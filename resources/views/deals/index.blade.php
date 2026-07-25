<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Pipeline') }}
            </h2>
            @if ($pipeline)
                <a href="{{ route('deals.create', ['pipeline' => $pipeline->id]) }}">
                    <x-primary-button>{{ __('+ Új üzlet') }}</x-primary-button>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'deal-created') {{ __('Üzlet létrehozva.') }} @endif
                    @if (session('status') === 'deal-updated') {{ __('Üzlet frissítve.') }} @endif
                    @if (session('status') === 'deal-moved') {{ __('Üzlet áthelyezve.') }} @endif
                    @if (session('status') === 'deal-deleted') {{ __('Üzlet törölve.') }} @endif
                </div>
            @endif

            {{-- Pipeline-választó fülek --}}
            <div class="flex gap-fluid-xs flex-wrap" role="tablist" aria-label="{{ __('Pipeline választó') }}">
                @foreach ($pipelines as $p)
                    <a href="{{ route('deals.index', ['pipeline' => $p->id]) }}"
                       role="tab"
                       aria-selected="{{ $pipeline && $pipeline->id === $p->id ? 'true' : 'false' }}"
                       class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $pipeline && $pipeline->id === $p->id ? 'bg-accent text-page' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                        {{ $p->name }}
                    </a>
                @endforeach
            </div>

            @if (! $pipeline)
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Nincs még pipeline beállítva.') }}
                </div>
            @else
                {{-- Kanban tábla --}}
                <div class="flex gap-fluid-sm overflow-x-auto pb-fluid-sm">
                    @foreach ($pipeline->stages as $stage)
                        <div class="flex-shrink-0 w-72 bg-sunken border border-line rounded-lg p-fluid-xs">
                            <div class="flex items-center justify-between px-2 py-1 mb-2">
                                <h3 class="font-semibold text-fluid-xs uppercase tracking-wide text-ink-soft">
                                    {{ $stage->name }}
                                </h3>
                                <span class="text-ink-muted text-fluid-xs">{{ $stage->deals->count() }}</span>
                            </div>

                            <div class="space-y-2">
                                @foreach ($stage->deals as $deal)
                                    <div class="bg-surface border border-line rounded-md p-fluid-xs">
                                        <a href="{{ route('deals.edit', $deal) }}" class="block font-medium text-ink text-fluid-base hover:text-accent">
                                            {{ $deal->title }}
                                        </a>

                                        @if ($deal->contact)
                                            <p class="text-ink-muted text-fluid-xs mt-1">{{ trim($deal->contact->first_name.' '.$deal->contact->last_name) }}</p>
                                        @endif

                                        @if ($deal->value)
                                            <p class="text-ink-soft text-fluid-xs">{{ number_format($deal->value, 0, ',', ' ') }} {{ $deal->currency }}</p>
                                        @endif

                                        {{-- Akadálymentes "mozgatás" — WCAG 2.5.7: a húzás-alapú lépéskeltetésnek
                                             legyen egyetlen-mutatóeszközös (pl. legördülő menüs) alternatívája is. --}}
                                        <form method="POST" action="{{ route('deals.move', $deal) }}" class="mt-2">
                                            @csrf
                                            @method('patch')
                                            <label class="sr-only" for="move-{{ $deal->id }}">{{ __('Áthelyezés lépésre') }}</label>
                                            <select id="move-{{ $deal->id }}" name="pipeline_stage_id" onchange="this.form.submit()"
                                                    class="w-full text-fluid-xs rounded-md border-line-strong bg-sunken text-ink-soft focus:border-line-strong focus:ring-line-strong">
                                                @foreach ($pipeline->stages as $targetStage)
                                                    <option value="{{ $targetStage->id }}" @selected($targetStage->id === $stage->id)>
                                                        {{ $targetStage->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                @endforeach

                                @if ($stage->deals->isEmpty())
                                    <p class="text-ink-muted text-fluid-xs italic px-2">{{ __('Nincs üzlet ebben a lépésben.') }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

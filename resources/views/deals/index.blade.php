<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Pipeline') }}
            </h2>
            <div class="flex gap-fluid-xs">
                @if ($pipeline)
                    <a href="{{ route('deals.index', ['pipeline' => $pipeline->id, 'view' => 'board']) }}">
                        <x-secondary-button type="button">{{ __('Tábla nézet') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('deals.create', ['pipeline' => $pipeline->id]) }}">
                        <x-primary-button>{{ __('+ Új üzlet') }}</x-primary-button>
                    </a>
                @endif
            </div>
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
                       class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $pipeline && $pipeline->id === $p->id ? 'bg-accent text-accent-ink' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                        {{ $p->name }}
                    </a>
                @endforeach
            </div>

            @if (! $pipeline)
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Nincs még pipeline beállítva.') }}
                </div>
            @else
                @php
                    $openCount = $pipeline->stages->sum(fn ($s) => $s->deals->where('status', 'open')->count());
                @endphp

                {{-- Összegző csempék — súlyozott (forecast) érték a stage-ekhez rendelt valószínűség alapján,
                     CRM best practice (pl. Pipedrive/Salesforce forecast-nézete). --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-fluid-sm">
                    <div class="bg-surface border border-line rounded-lg p-fluid-sm">
                        <p class="text-fluid-xs text-ink-muted uppercase tracking-wide">{{ __('Nyitott üzletek') }}</p>
                        <p class="text-fluid-xl font-semibold text-ink mt-1">{{ $openCount }}</p>
                    </div>
                    <div class="bg-surface border border-line rounded-lg p-fluid-sm">
                        <p class="text-fluid-xs text-ink-muted uppercase tracking-wide">{{ __('Nyitott érték összesen') }}</p>
                        <p class="text-fluid-xl font-semibold text-ink mt-1">{{ number_format($openValue, 0, ',', ' ') }} Ft</p>
                    </div>
                    <div class="bg-surface border border-line rounded-lg p-fluid-sm">
                        <p class="text-fluid-xs text-ink-muted uppercase tracking-wide">{{ __('Súlyozott (várható) érték') }}</p>
                        <p class="text-fluid-xl font-semibold text-ink mt-1">{{ number_format($weightedValue, 0, ',', ' ') }} Ft</p>
                    </div>
                </div>

                {{-- Lépésenkénti darabszám — áttekintéshez, nem görgetős oszlopok, csak egy sor jelvény. --}}
                <div class="flex flex-wrap gap-2">
                    @foreach ($pipeline->stages as $stage)
                        <span class="text-fluid-xs px-2 py-1 rounded bg-sunken text-ink-soft">
                            {{ $stage->name }}: <strong class="text-ink">{{ $stage->deals->count() }}</strong>
                        </span>
                    @endforeach
                </div>

                @php
                    $allDeals = $pipeline->stages->flatMap(fn ($stage) => $stage->deals->map(fn ($deal) => [$deal, $stage]));
                @endphp

                @if ($allDeals->isEmpty())
                    <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                        {{ __('Még nincs üzlet ebben a pipeline-ban.') }}
                        <a href="{{ route('deals.create', ['pipeline' => $pipeline->id]) }}" class="text-accent underline">{{ __('Vedd fel az elsőt.') }}</a>
                    </div>
                @else
                    {{-- Asztali táblázat --}}
                    <div class="hidden md:block bg-surface border border-line rounded-lg overflow-hidden">
                        <table class="w-full text-fluid-base">
                            <thead>
                                <tr class="border-b border-line text-left text-fluid-xs text-ink-muted uppercase tracking-wide">
                                    <th class="px-4 py-2">{{ __('Cím') }}</th>
                                    <th class="px-4 py-2">{{ __('Kontakt') }}</th>
                                    <th class="px-4 py-2">{{ __('Lépés') }}</th>
                                    <th class="px-4 py-2">{{ __('Érték') }}</th>
                                    <th class="px-4 py-2">{{ __('Napja ebben a lépésben') }}</th>
                                    <th class="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allDeals as [$deal, $stage])
                                    <tr class="border-b border-line last:border-0 hover:bg-surface-hover">
                                        <td class="px-4 py-2">
                                            <a href="{{ route('deals.edit', $deal) }}" class="font-medium text-ink hover:text-accent">{{ $deal->title }}</a>
                                        </td>
                                        <td class="px-4 py-2 text-ink-soft">
                                            {{ $deal->contact ? trim($deal->contact->first_name.' '.$deal->contact->last_name) : '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <form method="POST" action="{{ route('deals.move', $deal) }}">
                                                @csrf
                                                @method('patch')
                                                <label class="sr-only" for="move-{{ $deal->id }}">{{ __('Áthelyezés lépésre') }}</label>
                                                <select id="move-{{ $deal->id }}" name="pipeline_stage_id" onchange="this.form.submit()"
                                                        class="text-fluid-xs rounded-md border-line-strong bg-sunken text-ink-soft focus:border-line-strong focus:ring-line-strong">
                                                    @foreach ($pipeline->stages as $targetStage)
                                                        <option value="{{ $targetStage->id }}" @selected($targetStage->id === $stage->id)>
                                                            {{ $targetStage->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                        <td class="px-4 py-2 text-ink-soft">
                                            {{ $deal->value ? number_format($deal->value, 0, ',', ' ').' Ft' : '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            @if ($deal->status === 'open')
                                                @php $days = $deal->daysInStage(); @endphp
                                                <span class="{{ $days >= 14 ? 'text-warning font-medium' : 'text-ink-muted' }}">
                                                    {{ $days }} {{ __('nap') }}@if ($days >= 14) — {{ __('elakadt?') }}@endif
                                                </span>
                                            @else
                                                <span class="text-ink-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <a href="{{ route('deals.edit', $deal) }}" class="text-accent underline text-fluid-xs">{{ __('Szerkesztés') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobil kártyanézet --}}
                    <div class="md:hidden space-y-2">
                        @foreach ($allDeals as [$deal, $stage])
                            <div class="bg-surface border border-line rounded-lg p-fluid-sm">
                                <a href="{{ route('deals.edit', $deal) }}" class="font-medium text-ink text-fluid-base hover:text-accent">{{ $deal->title }}</a>
                                @if ($deal->contact)
                                    <p class="text-ink-muted text-fluid-xs mt-1">{{ trim($deal->contact->first_name.' '.$deal->contact->last_name) }}</p>
                                @endif
                                @if ($deal->value)
                                    <p class="text-ink-soft text-fluid-xs">{{ number_format($deal->value, 0, ',', ' ') }} Ft</p>
                                @endif
                                @if ($deal->status === 'open')
                                    @php $days = $deal->daysInStage(); @endphp
                                    <p class="text-fluid-xs mt-1 {{ $days >= 14 ? 'text-warning font-medium' : 'text-ink-muted' }}">
                                        {{ $days }} {{ __('napja ebben a lépésben') }}@if ($days >= 14) — {{ __('elakadt?') }}@endif
                                    </p>
                                @endif
                                <form method="POST" action="{{ route('deals.move', $deal) }}" class="mt-2">
                                    @csrf
                                    @method('patch')
                                    <label class="sr-only" for="move-m-{{ $deal->id }}">{{ __('Áthelyezés lépésre') }}</label>
                                    <select id="move-m-{{ $deal->id }}" name="pipeline_stage_id" onchange="this.form.submit()"
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
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>

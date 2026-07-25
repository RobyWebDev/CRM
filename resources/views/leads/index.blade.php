<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Leadek') }}
            </h2>
            <a href="{{ route('leads.create') }}">
                <x-primary-button>{{ __('+ Új lead') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'lead-created') {{ __('Lead létrehozva.') }} @endif
                    @if (session('status') === 'lead-updated') {{ __('Lead frissítve.') }} @endif
                    @if (session('status') === 'lead-deleted') {{ __('Lead törölve.') }} @endif
                </div>
            @endif

            <div class="flex gap-fluid-xs flex-wrap">
                @foreach (['' => 'Nyitott', 'all' => 'Összes', 'new' => 'Új', 'contacted' => 'Felvéve kapcsolat', 'qualified' => 'Minősített', 'unqualified' => 'Elutasított', 'converted' => 'Konvertált'] as $value => $label)
                    <a href="{{ route('leads.index', array_filter(['status' => $value])) }}"
                       class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $status === $value ? 'bg-accent text-accent-ink' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            @if ($leads->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Nincs ilyen állapotú lead.') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($leads as $lead)
                        <a href="{{ route('leads.edit', $lead) }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-sm transition">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-ink text-fluid-base">
                                    {{ $lead->full_name }}
                                </p>
                                @if (! is_null($lead->score))
                                    <span class="text-fluid-xs text-ink-muted">{{ $lead->score }}/100</span>
                                @endif
                            </div>
                            @if ($lead->company)
                                <p class="text-ink-muted text-fluid-xs">{{ $lead->company }}</p>
                            @endif
                            @if ($lead->serviceType)
                                <p class="text-ink-soft text-fluid-xs mt-1">{{ $lead->serviceType->name }}</p>
                            @endif
                            <p class="text-fluid-xs mt-2 inline-block px-2 py-0.5 rounded bg-sunken text-ink-soft">
                                {{ __(match ($lead->status) {
                                    'new' => 'Új',
                                    'contacted' => 'Felvéve kapcsolat',
                                    'qualified' => 'Minősített',
                                    'unqualified' => 'Elutasított',
                                    'converted' => 'Konvertált',
                                    default => $lead->status,
                                }) }}
                            </p>
                        </a>
                    @endforeach
                </div>

                <div>{{ $leads->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

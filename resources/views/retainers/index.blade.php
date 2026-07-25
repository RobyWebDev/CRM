<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Ismétlődő megbízások (retainerek)') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'retainer-updated') {{ __('Retainer frissítve.') }} @endif
                    @if (session('status') === 'retainer-deleted') {{ __('Retainer törölve.') }} @endif
                </div>
            @endif

            <div class="flex gap-fluid-xs flex-wrap">
                @foreach (['' => 'Aktív', 'all' => 'Összes', 'paused' => 'Szüneteltetve', 'ended' => 'Lezárva'] as $value => $label)
                    <a href="{{ route('retainers.index', array_filter(['status' => $value])) }}"
                       class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $status === $value ? 'bg-accent text-accent-ink' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            @if ($retainers->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Nincs ilyen állapotú retainer. Egy marketing/SEO deal "nyert" lépésre mozgatásakor automatikusan létrejön ide.') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($retainers as $retainer)
                        <a href="{{ route('retainers.show', $retainer) }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-sm transition">
                            <p class="font-semibold text-ink text-fluid-base">{{ $retainer->title }}</p>
                            @if ($retainer->contact)
                                <p class="text-ink-muted text-fluid-xs mt-1">{{ $retainer->contact->full_name }}</p>
                            @endif
                            @if ($retainer->monthly_fee)
                                <p class="text-ink-soft text-fluid-xs">{{ number_format($retainer->monthly_fee, 0, ',', ' ') }} Ft / hó</p>
                            @endif
                            <p class="text-fluid-xs mt-2 inline-block px-2 py-0.5 rounded bg-sunken text-ink-soft">
                                {{ __(match ($retainer->status) {
                                    'active' => 'Aktív',
                                    'paused' => 'Szüneteltetve',
                                    'ended' => 'Lezárva',
                                    default => $retainer->status,
                                }) }}
                            </p>
                        </a>
                    @endforeach
                </div>

                <div>{{ $retainers->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

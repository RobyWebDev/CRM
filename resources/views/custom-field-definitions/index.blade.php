<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Egyedi mezők') }}
            </h2>
            <a href="{{ route('custom-field-definitions.create') }}">
                <x-primary-button>{{ __('+ Új egyedi mező') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'custom-field-created') {{ __('Egyedi mező létrehozva.') }} @endif
                    @if (session('status') === 'custom-field-updated') {{ __('Egyedi mező frissítve.') }} @endif
                    @if (session('status') === 'custom-field-deleted') {{ __('Egyedi mező törölve.') }} @endif
                </div>
            @endif

            <p class="text-ink-muted text-fluid-xs">
                {{ __('Itt fejlesztői beavatkozás nélkül vehetsz fel új mezőket a kontaktokhoz vagy üzletekhez — akár csak egy adott szolgáltatás-típusnál, akár mindenhol. A felvett mezők automatikusan megjelennek a megfelelő űrlapokon.') }}
            </p>

            @if ($definitions->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Még nincs egyetlen egyedi mező sem.') }}
                    <a href="{{ route('custom-field-definitions.create') }}" class="text-accent underline">{{ __('Vedd fel az elsőt.') }}</a>
                </div>
            @else
                @foreach ($definitions as $entityType => $group)
                    <div class="bg-surface border border-line rounded-lg p-fluid-md">
                        <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ $entityTypes[$entityType] ?? $entityType }}</h3>
                        <ul class="divide-y divide-line">
                            @foreach ($group as $definition)
                                <li class="py-2 flex items-center justify-between gap-2 flex-wrap">
                                    <div>
                                        <span class="text-ink font-medium">{{ $definition->label }}</span>
                                        <span class="text-ink-muted text-fluid-xs">(
                                            {{ $definition->field_type }}
                                            @if ($definition->is_required), {{ __('kötelező') }}@endif
                                            @if ($definition->serviceType), {{ $definition->serviceType->name }}@endif
                                        )</span>
                                    </div>
                                    <div class="flex gap-fluid-xs">
                                        <a href="{{ route('custom-field-definitions.edit', $definition) }}" class="text-accent underline text-fluid-xs">{{ __('Szerkesztés') }}</a>
                                        <form method="POST" action="{{ route('custom-field-definitions.destroy', $definition) }}" onsubmit="return confirm('{{ __('Biztosan törlöd? A már felvitt értékek megmaradnak a rekordokon, csak az űrlapról tűnik el.') }}')">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-danger underline text-fluid-xs">{{ __('Törlés') }}</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>

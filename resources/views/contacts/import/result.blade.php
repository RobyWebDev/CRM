<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Import eredménye') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-center">
                    <p class="text-fluid-lg font-semibold text-success">{{ $imported }}</p>
                    <p class="text-fluid-xs text-ink-muted">{{ __('Sikeresen importált kontakt') }}</p>
                </div>
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-center">
                    <p class="text-fluid-lg font-semibold text-warning">{{ $skippedDuplicates }}</p>
                    <p class="text-fluid-xs text-ink-muted">{{ __('Kihagyva (már létező e-mail miatt)') }}</p>
                </div>
            </div>

            @if (! empty($errors))
                <div class="bg-surface border border-line rounded-lg p-fluid-md">
                    <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Hibás/kihagyott sorok') }}</h3>
                    <ul class="list-disc list-inside text-fluid-xs text-ink-soft space-y-1">
                        @foreach ($errors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ route('contacts.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a kontaktokhoz') }}</a>
        </div>
    </div>
</x-app-layout>

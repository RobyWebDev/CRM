<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Kontaktok importálása CSV-ből') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'import-file-expired')
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-warning mb-fluid-sm">
                    {{ __('A feltöltött fájl már nem elérhető (túl sok idő telt el az előnézet óta) — töltsd fel újra.') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contacts.import.preview') }}" enctype="multipart/form-data"
                  class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf

                <p class="text-ink-muted text-fluid-xs">
                    {{ __('Meglévő Excel/Sheet listád első sora legyen a fejléc (oszlopnevek). A feltöltés után kiválaszthatod, melyik oszlop melyik mezőnek felel meg, és megnézheted az első pár sor előnézetét, mielőtt ténylegesen elindítanád az importot.') }}
                </p>

                <div>
                    <x-input-label for="file" :value="__('CSV-fájl')" />
                    <input type="file" id="file" name="file" accept=".csv,text/csv" required
                           class="block mt-1 w-full text-fluid-base text-ink-soft file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-sunken file:text-ink-soft">
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('contacts.index') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Tovább a mezőtérképezéshez') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

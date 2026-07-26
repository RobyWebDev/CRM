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

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2 mb-fluid-sm">
                <h3 class="font-semibold text-fluid-base text-ink">{{ __('Mit fogadunk el?') }}</h3>
                <p class="text-ink-soft text-fluid-xs">
                    {{ __('Gyakorlatilag BÁRMILYEN CSV feltölthető — nem kell pontos, előírt oszlopnevet használnod. A feltöltés utáni képernyőn Te választod ki (legördülőkből), melyik saját oszlopod melyik mezőnek felel meg; amit nem képezel le, egyszerűen kimarad. Ha a fejléceid amúgy is hasonlítanak a lenti nevekre (pl. "Keresztnév", "E-mail"), ezt automatikusan ki is találjuk neked, de ez csak egy kényelmi tipp, nem elvárás.') }}
                </p>
                <ul class="list-disc list-inside text-ink-soft text-fluid-xs space-y-1">
                    <li>{{ __('Az első sor legyen a fejléc (oszlopnevek) — ez kötelező, a 2. sortól kezdve olvassuk az adatokat.') }}</li>
                    <li>{{ __('Elválasztó lehet vessző vagy pontosvessző — automatikusan felismerjük.') }}</li>
                    <li>{{ __('A kódolás (UTF-8 vagy a magyar Excel-exportoknál szokásos Windows-1250) szintén automatikusan felismerve/konvertálva — nem kell vele foglalkoznod.') }}</li>
                    <li>{{ __('Ha egy cellában vesszővel felsorolt több értéket adsz meg (pl. több címke egy kontaktnál), és a fájlod vesszővel tagolt, azt a cellát idézőjelbe kell tenni — Excel/Sheets ezt automatikusan megteszi, ha onnan mented CSV-be.') }}</li>
                    <li>{{ __('A születésnapnál az ÉÉÉÉ-HH-NN formátum (pl. 1990-05-12) a legbiztosabb — más formátumnál előfordulhat, hogy az adott sor hibásként kimarad.') }}</li>
                    <li>{{ __('Duplikátum-kezelés: ha egy sor e-mail címe már szerepel a rendszerben, az a sor kimarad (nem írja felül a meglévő kontaktot).') }}</li>
                </ul>
                <p class="text-ink-soft text-fluid-xs">
                    {{ __('A választható mezők') }}: {{ implode(', ', array_values($templateHeaders)) }}@if ($customFieldDefinitions->isNotEmpty()), {{ $customFieldDefinitions->pluck('label')->implode(', ') }} ({{ __('egyedi mezők') }})@endif.
                </p>
                <a href="{{ route('contacts.import.template') }}" class="text-accent underline text-fluid-xs inline-block">
                    {{ __('Minta CSV letöltése (kitöltött példasorral)') }}
                </a>
            </div>

            <form method="POST" action="{{ route('contacts.import.preview') }}" enctype="multipart/form-data"
                  class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf

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

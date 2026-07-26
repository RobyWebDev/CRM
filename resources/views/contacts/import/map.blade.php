<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Mezőtérképezés és előnézet') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <p class="text-ink-muted text-fluid-xs">
                {{ __('A fájlban :total sor található. Válaszd ki, melyik oszlop melyik mezőnek felel meg — amit nem képezel le, az kimarad az importból. Az alábbi táblázatban az első néhány sor előnézete látható a jelenlegi térképezéssel.', ['total' => $totalRows]) }}
            </p>

            <form method="POST" action="{{ route('contacts.import.store') }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                <input type="hidden" name="filename" value="{{ $filename }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($headers as $header)
                        <div>
                            <x-input-label :value="$header" />
                            <select name="mapping[{{ $header }}]"
                                    class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                                <option value="__skip__">{{ __('— Kihagyás —') }}</option>
                                @foreach ($targetFields as $field => $label)
                                    <option value="{{ $field }}" @selected($mapping[$header] === $field)>{{ __($label) }}</option>
                                @endforeach
                                @foreach ($customFieldDefinitions as $definition)
                                    <option value="custom:{{ $definition->field_key }}">{{ __('Egyedi mező') }}: {{ $definition->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-fluid-xs">
                        <thead>
                            <tr class="border-b border-line">
                                @foreach ($headers as $header)
                                    <th class="text-left px-2 py-1 text-ink-muted font-medium">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($previewRows as $row)
                                <tr class="border-b border-line">
                                    @foreach ($headers as $header)
                                        <td class="px-2 py-1 text-ink-soft whitespace-nowrap">{{ $row[$header] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('contacts.import.create') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Import indítása') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

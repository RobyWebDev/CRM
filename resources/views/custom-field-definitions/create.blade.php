<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Új egyedi mező') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('custom-field-definitions.store') }}"
                  class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm"
                  x-data="{ fieldType: '{{ old('field_type', 'text') }}' }">
                @csrf

                <div>
                    <x-input-label for="entity_type" :value="__('Hol jelenjen meg?')" />
                    <select id="entity_type" name="entity_type" required
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        @foreach ($entityTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('entity_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('entity_type')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="service_type_id" :value="__('Csak egy adott szolgáltatás-típusnál (opcionális)')" />
                    <select id="service_type_id" name="service_type_id"
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— mindenhol —') }}</option>
                        @foreach ($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}" @selected(old('service_type_id') == $serviceType->id)>{{ $serviceType->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('service_type_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="label" :value="__('Mező neve')" />
                    <x-text-input id="label" name="label" class="block mt-1 w-full" :value="old('label')" placeholder="{{ __('pl. Felmérés pontszám, Domain név...') }}" required autofocus />
                    <x-input-error :messages="$errors->get('label')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="field_type" :value="__('Mező típusa')" />
                    <select id="field_type" name="field_type" x-model="fieldType" required
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        @foreach ($fieldTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('field_type')" class="mt-2" />
                </div>

                <div x-show="fieldType === 'select' || fieldType === 'multiselect'">
                    <x-input-label for="options" :value="__('Választható értékek (vesszővel elválasztva)')" />
                    <x-text-input id="options" name="options" class="block mt-1 w-full" :value="old('options')" placeholder="{{ __('pl. kezdő, haladó, profi') }}" />
                    <x-input-error :messages="$errors->get('options')" class="mt-2" />
                </div>

                <label class="flex items-center gap-2 text-fluid-base text-ink">
                    <input type="checkbox" name="is_required" value="1" @checked(old('is_required'))
                           class="rounded border-line-strong text-accent focus:ring-line-strong">
                    {{ __('Kötelező mező legyen') }}
                </label>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('custom-field-definitions.index') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

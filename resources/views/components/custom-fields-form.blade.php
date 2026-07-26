{{--
    A "kódolás nélküli univerzalitás" mechanizmusa (crm_projekt.md 3. szekció) —
    az admin által (kód nélkül) felvett egyedi mező-definíciók (CustomFieldDefinition)
    alapján dinamikusan renderel beviteli mezőket, bekötve a Kontakt/Üzlet
    felvételi és szerkesztő űrlapjába. Lásd App\Support\CustomFieldFormHelper.
--}}
@props(['entityType', 'serviceTypeId' => null, 'values' => []])

@php
    $definitions = \App\Support\CustomFieldFormHelper::definitionsFor($entityType, $serviceTypeId);
@endphp

@if ($definitions->isNotEmpty())
    <div class="space-y-fluid-sm border border-line rounded-md p-fluid-sm">
        <h3 class="font-semibold text-fluid-base text-ink">{{ __('Egyedi mezők') }}</h3>

        @foreach ($definitions as $definition)
            @php
                $fieldName = 'custom_fields['.$definition->field_key.']';
                $fieldId = 'custom_fields_'.$definition->field_key;
                $currentValue = $values[$definition->field_key] ?? null;
                $oldValue = old('custom_fields.'.$definition->field_key, $currentValue);
            @endphp
            <div>
                @if ($definition->field_type !== 'boolean')
                    <x-input-label :for="$fieldId" :value="$definition->label.($definition->is_required ? ' *' : '')" />
                @endif

                @switch($definition->field_type)
                    @case('textarea')
                        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" rows="3"
                                  class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ $oldValue }}</textarea>
                        @break

                    @case('boolean')
                        <label class="flex items-center gap-2 text-fluid-base text-ink">
                            <input type="checkbox" id="{{ $fieldId }}" name="{{ $fieldName }}" value="1" @checked($oldValue)
                                   class="rounded border-line-strong text-accent focus:ring-line-strong">
                            {{ $definition->label }}{{ $definition->is_required ? ' *' : '' }}
                        </label>
                        @break

                    @case('select')
                        <select id="{{ $fieldId }}" name="{{ $fieldName }}"
                                class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                            <option value="">{{ __('— nincs —') }}</option>
                            @foreach ($definition->options ?? [] as $option)
                                <option value="{{ $option }}" @selected($oldValue === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('multiselect')
                        <div class="flex flex-wrap gap-3 mt-1">
                            @foreach ($definition->options ?? [] as $option)
                                <label class="flex items-center gap-2 text-fluid-base text-ink">
                                    <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $option }}"
                                           @checked(is_array($oldValue) && in_array($option, $oldValue, true))
                                           class="rounded border-line-strong text-accent focus:ring-line-strong">
                                    {{ $option }}
                                </label>
                            @endforeach
                        </div>
                        @break

                    @case('number')
                        <x-text-input type="number" :id="$fieldId" :name="$fieldName" class="block mt-1 w-full" :value="$oldValue" />
                        @break

                    @case('date')
                        <x-text-input type="date" :id="$fieldId" :name="$fieldName" class="block mt-1 w-full" :value="$oldValue" />
                        @break

                    @case('datetime')
                        <x-text-input type="datetime-local" :id="$fieldId" :name="$fieldName" class="block mt-1 w-full" :value="$oldValue" />
                        @break

                    @case('url')
                        <x-text-input type="url" :id="$fieldId" :name="$fieldName" class="block mt-1 w-full" :value="$oldValue" placeholder="https://..." />
                        @break

                    @default
                        <x-text-input :id="$fieldId" :name="$fieldName" class="block mt-1 w-full" :value="$oldValue" />
                @endswitch

                <x-input-error :messages="$errors->get('custom_fields.'.$definition->field_key)" class="mt-2" />
            </div>
        @endforeach
    </div>
@endif

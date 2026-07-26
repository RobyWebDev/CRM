{{-- Az egyedi mezők (CustomFieldDefinition) kitöltött értékeinek megjelenítése, csak-olvasható nézeten. --}}
@props(['entityType', 'serviceTypeId' => null, 'values' => []])

@php
    $definitions = \App\Support\CustomFieldFormHelper::definitionsFor($entityType, $serviceTypeId);
@endphp

@foreach ($definitions as $definition)
    @php $value = $values[$definition->field_key] ?? null; @endphp
    @if ($value !== null && $value !== '' && $value !== [])
        <p>
            <span class="text-ink-muted text-fluid-xs">{{ $definition->label }}:</span>
            <span class="text-ink">
                @if ($definition->field_type === 'boolean')
                    {{ $value ? __('Igen') : __('Nem') }}
                @elseif ($definition->field_type === 'date')
                    {{ \Illuminate\Support\Carbon::parse($value)->format('Y.m.d.') }}
                @elseif ($definition->field_type === 'datetime')
                    {{ \Illuminate\Support\Carbon::parse($value)->format('Y.m.d. H:i') }}
                @elseif (is_array($value))
                    {{ implode(', ', $value) }}
                @else
                    {{ $value }}
                @endif
            </span>
        </p>
    @endif
@endforeach

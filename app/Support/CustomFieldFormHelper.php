<?php

namespace App\Support;

use App\Models\CustomFieldDefinition;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * A "kódolás nélküli univerzalitás" mechanizmusa (crm_projekt.md 3. szekció,
 * kiemelt elvárás) — a `custom_field_definitions` tábla és az `App\Models\
 * CustomFieldDefinition` modell a projekt legelejétől (2026-07-25) létezett,
 * de egyetlen forma sem jelenítette meg/mentette a definiált mezőket — ez
 * a segédosztály köti be ténylegesen (2026-07-26, önálló kritikai audit).
 */
class CustomFieldFormHelper
{
    public static function definitionsFor(string $entityType, ?int $serviceTypeId): Collection
    {
        return CustomFieldDefinition::where('entity_type', $entityType)
            ->where(function ($query) use ($serviceTypeId) {
                $query->whereNull('service_type_id');
                if ($serviceTypeId) {
                    $query->orWhere('service_type_id', $serviceTypeId);
                }
            })
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validationRules(string $entityType, ?int $serviceTypeId): array
    {
        $rules = [];

        foreach (self::definitionsFor($entityType, $serviceTypeId) as $definition) {
            $required = $definition->is_required ? 'required' : 'nullable';

            $typeRule = match ($definition->field_type) {
                'number' => 'numeric',
                'date' => 'date',
                'boolean' => 'boolean',
                'url' => 'url',
                'select' => Rule::in($definition->options ?? []),
                'multiselect' => 'array',
                default => 'string',
            };

            $rules["custom_fields.{$definition->field_key}"] = [$required, $typeRule];
        }

        return $rules;
    }
}

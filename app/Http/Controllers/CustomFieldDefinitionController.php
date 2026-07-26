<?php

namespace App\Http\Controllers;

use App\Models\CustomFieldDefinition;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Egyedi mezők admin-felülete — a "kódolás nélküli univerzalitás" mechanizmusa
 * (crm_projekt.md 3. szekció, kiemelt elvárás): Rob fejlesztői beavatkozás
 * nélkül vehet fel új mezőt bármelyik szakmai profiljához. A `custom_field_
 * definitions` tábla/modell a projekt legelejétől létezett, de eddig nem volt
 * hozzá felület (2026-07-26, önálló kritikai audit — lásd docs/haladasi-naplo.md).
 */
class CustomFieldDefinitionController extends Controller
{
    public const ENTITY_TYPES = ['contact' => 'Kontakt', 'deal' => 'Üzlet'];

    public const FIELD_TYPES = [
        'text' => 'Rövid szöveg',
        'textarea' => 'Hosszú szöveg',
        'number' => 'Szám',
        'date' => 'Dátum',
        'boolean' => 'Igen/Nem',
        'select' => 'Választólista (egy érték)',
        'multiselect' => 'Választólista (több érték)',
        'url' => 'Weboldal-cím',
    ];

    public function index(): View
    {
        $definitions = CustomFieldDefinition::with('serviceType')
            ->orderBy('entity_type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('entity_type');

        return view('custom-field-definitions.index', [
            'definitions' => $definitions,
            'entityTypes' => self::ENTITY_TYPES,
        ]);
    }

    public function create(): View
    {
        return view('custom-field-definitions.create', [
            'entityTypes' => self::ENTITY_TYPES,
            'fieldTypes' => self::FIELD_TYPES,
            'serviceTypes' => ServiceType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDefinition($request);

        $data['field_key'] = $this->generateFieldKey($data['entity_type'], $data['label']);

        CustomFieldDefinition::create($data);

        return redirect()->route('custom-field-definitions.index')->with('status', 'custom-field-created');
    }

    public function edit(CustomFieldDefinition $customFieldDefinition): View
    {
        return view('custom-field-definitions.edit', [
            'definition' => $customFieldDefinition,
            'entityTypes' => self::ENTITY_TYPES,
            'fieldTypes' => self::FIELD_TYPES,
            'serviceTypes' => ServiceType::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, CustomFieldDefinition $customFieldDefinition): RedirectResponse
    {
        $data = $this->validateDefinition($request);

        // A field_key (a JSON-ban ténylegesen tárolt kulcs) NEM változik a névvel —
        // ha változna, minden meglévő rekordon elveszne a hozzá tartozó érték.
        $customFieldDefinition->update($data);

        return redirect()->route('custom-field-definitions.index')->with('status', 'custom-field-updated');
    }

    public function destroy(CustomFieldDefinition $customFieldDefinition): RedirectResponse
    {
        $customFieldDefinition->delete();

        return redirect()->route('custom-field-definitions.index')->with('status', 'custom-field-deleted');
    }

    private function validateDefinition(Request $request): array
    {
        $data = $request->validate([
            'entity_type' => ['required', Rule::in(array_keys(self::ENTITY_TYPES))],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', Rule::in(array_keys(self::FIELD_TYPES))],
            'options' => ['nullable', 'string'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        // Az "options" mező vesszővel elválasztott szöveg az űrlapon (pl. "kezdő,
        // haladó, profi"), de a modellen JSON tömbként tárolódik — csak a select/
        // multiselect típusoknál van értelme.
        $data['options'] = in_array($data['field_type'], ['select', 'multiselect'], true) && ! empty($data['options'])
            ? collect(explode(',', $data['options']))->map(fn ($o) => trim($o))->filter()->values()->all()
            : null;

        $data['is_required'] = ! empty($data['is_required']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    /**
     * A field_key-t NEM a felhasználó adja meg (ő csak egy emberi olvasható "label"-t
     * ír be, "kódolás nélküli" szellemben) — a rendszer generálja belőle, és ütközés
     * esetén sorszámmal egyedivé teszi (account+entity_type szinten egyedi kulcs).
     */
    private function generateFieldKey(string $entityType, string $label): string
    {
        $base = Str::slug($label, '_') ?: 'mezo';
        $key = $base;
        $suffix = 2;

        while (CustomFieldDefinition::where('entity_type', $entityType)->where('field_key', $key)->exists()) {
            $key = $base.'_'.$suffix;
            $suffix++;
        }

        return $key;
    }
}

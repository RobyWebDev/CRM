{{--
    Kereszt-/vezetéknév mezők nyelv szerint helyes sorrendben (Rob kérése, 2026-07-25):
    magyarul a vezetéknév elöl (pl. "Kovács János"), angolul (US) a keresztnév elöl
    (pl. "John Smith"). Lásd App\Models\Concerns\HasPersonName::nameOrder().
--}}
@props(['firstName' => null, 'lastName' => null])

@php
    $hungarianOrder = \App\Models\Concerns\HasPersonName::nameOrder();

    $firstNameField = [
        'label' => __('Keresztnév'),
        'name' => 'first_name',
        'value' => $firstName,
        'required' => true,
    ];
    $lastNameField = [
        'label' => __('Vezetéknév'),
        'name' => 'last_name',
        'value' => $lastName,
        'required' => false,
    ];

    $fields = $hungarianOrder ? [$lastNameField, $firstNameField] : [$firstNameField, $lastNameField];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
    @foreach ($fields as $index => $field)
        <div>
            <x-input-label :for="$field['name']" :value="$field['label']" />
            <x-text-input
                :id="$field['name']"
                :name="$field['name']"
                class="block mt-1 w-full"
                :value="$field['value']"
                :required="$field['required']"
                :autofocus="$index === 0"
            />
            <x-input-error :messages="$errors->get($field['name'])" class="mt-2" />
        </div>
    @endforeach
</div>

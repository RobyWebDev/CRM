{{--
    "Válassz meglévőt VAGY hozz létre újat" lenyíló mező (Rob kérése, 2026-07-26) —
    a nagy CRM-ek "+ Új létrehozása..." mintája: ha a keresett dolog (pl. kampány,
    szervezet) még nincs a listában, itt egyetlen kattintással létrehozható, VALÓDI
    (nem szabad szöveges) rekordként — lásd App\Support\SelectOrCreate.
--}}
@props(['name', 'options', 'selected' => null, 'newFieldName', 'newPlaceholder', 'placeholder' => '— nincs —', 'newOptionLabel' => '+ Új létrehozása...'])

<div x-data="{ value: @js(old($name, $selected) ?? '') }">
    <select name="{{ $name }}" x-model="value"
            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
        @endforeach
        <option value="{{ \App\Support\SelectOrCreate::NEW_OPTION_VALUE }}">{{ $newOptionLabel }}</option>
    </select>
    <x-input-error :messages="$errors->get($name)" class="mt-2" />

    <div x-show="value === '{{ \App\Support\SelectOrCreate::NEW_OPTION_VALUE }}'" class="mt-2">
        <x-text-input :name="$newFieldName" class="block w-full" :value="old($newFieldName)" :placeholder="$newPlaceholder" />
        <x-input-error :messages="$errors->get($newFieldName)" class="mt-2" />
    </div>
</div>

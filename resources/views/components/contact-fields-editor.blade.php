{{--
    Tetszőleges számú, elnevezhető elérhetőség/mező szerkesztője — Google Címtár-mintára
    (Rob kérése, 2026-07-26): a fő e-mail/telefon/cím mező MELLETT bárki hozzáadhat
    továbbiakat, saját elnevezéssel (pl. "Mobil"/"Vezetékes", "Helyszín"/"Számlázási cím"),
    vagy teljesen szabad, egyedi mezőt (pl. "Adószám") — amíg nincs elnevezve, a rendszer
    "Egyedi mező 1", "Egyedi mező 2" stb. néven jeleníti meg (lásd Contact modell).
--}}
@props(['fields' => []])

@php
    $initialFields = collect($fields)->map(fn ($f) => [
        'type' => $f['type'] ?? 'email',
        'label' => $f['label'] ?? '',
        'value' => $f['value'] ?? '',
    ])->values()->all();
@endphp

<div x-data="{ fields: {{ \Illuminate\Support\Js::from($initialFields) }} }" class="space-y-2">
    <x-input-label :value="__('További elérhetőségek / egyedi mezők')" />
    <p class="text-ink-muted text-fluid-xs">{{ __('Pl. második telefonszám ("Mobil"/"Vezetékes"), több cím ("Helyszín"/"Számlázási cím"), adószám, vagy bármi más, amit elnevezel.') }}</p>

    <template x-for="(field, index) in fields" :key="index">
        <div class="grid grid-cols-1 sm:grid-cols-[8rem_10rem_1fr_auto] gap-2 items-start">
            <select :name="'contact_fields[' + index + '][type]'" x-model="field.type"
                    class="rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                <option value="email">{{ __('E-mail') }}</option>
                <option value="phone">{{ __('Telefon') }}</option>
                <option value="address">{{ __('Cím') }}</option>
                <option value="custom">{{ __('Egyéb') }}</option>
            </select>
            <input type="text" :name="'contact_fields[' + index + '][label]'" x-model="field.label"
                   placeholder="{{ __('Elnevezés (pl. Mobil)') }}"
                   class="rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
            <input type="text" :name="'contact_fields[' + index + '][value]'" x-model="field.value"
                   placeholder="{{ __('Érték') }}"
                   class="rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
            <button type="button" @click="fields.splice(index, 1)"
                    class="text-ink-muted hover:text-danger text-fluid-xs px-2" aria-label="{{ __('Mező eltávolítása') }}">&times;</button>
        </div>
    </template>

    <x-secondary-button type="button" @click="fields.push({ type: 'email', label: '', value: '' })">
        {{ __('+ Elérhetőség/mező hozzáadása') }}
    </x-secondary-button>
</div>

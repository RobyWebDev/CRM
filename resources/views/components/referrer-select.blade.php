{{--
    "Ki ajánlotta?" mező — ha az ajánló még nincs felvéve kontaktként, itt egyetlen
    kattintással felvehető ÚJ kontaktként (Rob kérése, 2026-07-26), és rögtön ő lesz
    az ajánló — nem szabad szöveges bejegyzés, hanem valódi, később is szerkeszthető
    kontakt-rekord (lásd ContactController::store()/update()).
--}}
@props(['contacts', 'selected' => null])

<div x-data="{ value: @js(old('referred_by_contact_id', $selected) ?? '') }">
    <select name="referred_by_contact_id" x-model="value"
            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
        <option value="">{{ __('— nincs / nem ajánlás —') }}</option>
        @foreach ($contacts as $c)
            <option value="{{ $c->id }}">{{ $c->full_name }}</option>
        @endforeach
        <option value="{{ \App\Support\SelectOrCreate::NEW_OPTION_VALUE }}">{{ __('+ Új kontakt felvétele ajánlóként...') }}</option>
    </select>
    <x-input-error :messages="$errors->get('referred_by_contact_id')" class="mt-2" />

    <div x-show="value === '{{ \App\Support\SelectOrCreate::NEW_OPTION_VALUE }}'" class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
        <div>
            <x-text-input name="referrer_first_name" class="block w-full" :value="old('referrer_first_name')" placeholder="{{ __('Ajánló keresztneve') }}" />
            <x-input-error :messages="$errors->get('referrer_first_name')" class="mt-2" />
        </div>
        <x-text-input name="referrer_last_name" class="block w-full" :value="old('referrer_last_name')" placeholder="{{ __('Ajánló vezetékneve (opcionális)') }}" />
        <x-text-input name="referrer_email" type="email" class="block w-full" :value="old('referrer_email')" placeholder="{{ __('E-mail (opcionális)') }}" />
        <x-text-input name="referrer_phone" class="block w-full" :value="old('referrer_phone')" placeholder="{{ __('Telefon (opcionális)') }}" />
    </div>
</div>

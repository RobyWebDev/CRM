<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Kontakt szerkesztése') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('contacts.update', $contact) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <x-name-fields :first-name="old('first_name', $contact->first_name)" :last-name="old('last_name', $contact->last_name)" />

                <div>
                    <x-input-label for="job_title" :value="__('Beosztás')" />
                    <x-text-input id="job_title" name="job_title" class="block mt-1 w-full" :value="old('job_title', $contact->job_title)" />
                    <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="email" :value="__('E-mail')" />
                        <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $contact->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Telefon')" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $contact->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="birthday" :value="__('Születésnap')" />
                        <x-text-input id="birthday" type="date" name="birthday" class="block mt-1 w-full" :value="old('birthday', $contact->birthday?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('birthday')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="website" :value="__('Weboldal')" />
                        <x-text-input id="website" name="website" class="block mt-1 w-full" :value="old('website', $contact->website)" placeholder="https://..." />
                        <x-input-error :messages="$errors->get('website')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="address" :value="__('Cím')" />
                    <textarea id="address" name="address" rows="2" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('address', $contact->address) }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="tags" :value="__('Címkék (vesszővel elválasztva)')" />
                    <x-text-input id="tags" name="tags" class="block mt-1 w-full" :value="old('tags', $contact->tags->pluck('name')->implode(', '))" placeholder="{{ __('pl. vip, budapest, ajánlás') }}" />
                    <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="organization_id" :value="__('Szervezet')" />
                    <x-select-or-create
                        name="organization_id"
                        :options="$organizations->map(fn ($o) => ['id' => $o->id, 'label' => $o->name])"
                        :selected="$contact->organization_id"
                        new-field-name="new_organization_name"
                        new-placeholder="{{ __('Új szervezet neve') }}"
                        new-option-label="{{ __('+ Új szervezet...') }}"
                    />
                </div>

                <div>
                    <x-input-label for="referred_by_contact_id" :value="__('Ki ajánlotta?')" />
                    <x-referrer-select :contacts="$contacts" :selected="$contact->referred_by_contact_id" />
                </div>

                <x-contact-fields-editor :fields="old('contact_fields', $contact->contactFields->map(fn ($f) => ['type' => $f->type, 'label' => $f->label, 'value' => $f->value])->all())" />
                <x-input-error :messages="$errors->get('contact_fields.*.value')" class="mt-2" />

                <div class="border border-line rounded-md p-fluid-sm space-y-2">
                    <label class="flex items-center gap-2 text-fluid-base text-ink">
                        <input type="checkbox" name="gdpr_consent_given" value="1" @checked(old('gdpr_consent_given', $contact->gdpr_consent_at !== null))
                               class="rounded border-line-strong text-accent focus:ring-line-strong">
                        {{ __('GDPR adatkezelési hozzájárulás megadva') }}
                    </label>
                    @if ($contact->gdpr_consent_at)
                        <p class="text-ink-muted text-fluid-xs">{{ __('Rögzítve') }}: {{ $contact->gdpr_consent_at->format('Y.m.d. H:i') }}</p>
                    @endif
                    <div>
                        <x-input-label for="gdpr_consent_note" :value="__('Hozzájárulás jellege/formája (opcionális)')" />
                        <x-text-input id="gdpr_consent_note" name="gdpr_consent_note" class="block mt-1 w-full" :value="old('gdpr_consent_note', $contact->gdpr_consent_note)" placeholder="{{ __('pl. e-mailben, szerződésben, szóban...') }}" />
                        <x-input-error :messages="$errors->get('gdpr_consent_note')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('contacts.show', $contact) }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

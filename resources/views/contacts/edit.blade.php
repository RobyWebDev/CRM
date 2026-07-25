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
                    <select id="organization_id" name="organization_id" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— nincs —') }}</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected(old('organization_id', $contact->organization_id) == $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('contacts.show', $contact) }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

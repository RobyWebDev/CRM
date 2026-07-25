<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Új lead') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('leads.store') }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf

                <x-name-fields :first-name="old('first_name')" :last-name="old('last_name')" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="email" :value="__('E-mail')" />
                        <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Telefon')" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="company" :value="__('Cég')" />
                        <x-text-input id="company" name="company" class="block mt-1 w-full" :value="old('company')" />
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="source" :value="__('Forrás')" />
                        <x-text-input id="source" name="source" class="block mt-1 w-full" :value="old('source')" placeholder="{{ __('pl. weboldal, ajánlás...') }}" />
                        <x-input-error :messages="$errors->get('source')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="service_type_id" :value="__('Érdeklődik iránta')" />
                    <select id="service_type_id" name="service_type_id" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— nincs megadva —') }}</option>
                        @foreach ($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}" @selected(old('service_type_id') == $serviceType->id)>{{ $serviceType->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('service_type_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Jegyzet')" />
                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('leads.index') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

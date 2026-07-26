<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Új kampány') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('campaigns.store') }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Kampány neve')" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" placeholder="{{ __('pl. 2026 nyári Facebook-hirdetés') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="type" :value="__('Típus')" />
                        <x-text-input id="type" name="type" class="block mt-1 w-full" :value="old('type')" placeholder="{{ __('pl. hirdetés, hideghívás, ajánlás...') }}" />
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="started_at" :value="__('Indulás dátuma')" />
                        <x-text-input id="started_at" type="date" name="started_at" class="block mt-1 w-full" :value="old('started_at')" />
                        <x-input-error :messages="$errors->get('started_at')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="cost" :value="__('Költség (Ft, opcionális)')" />
                    <x-text-input id="cost" type="number" step="1" min="0" name="cost" class="block mt-1 w-full sm:w-40" :value="old('cost')" />
                    <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('campaigns.index') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

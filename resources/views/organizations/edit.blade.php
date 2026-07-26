<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Szervezet szerkesztése') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('organizations.update', $organization) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="name" :value="__('Név')" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $organization->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="industry" :value="__('Iparág')" />
                    <x-text-input id="industry" name="industry" class="block mt-1 w-full" :value="old('industry', $organization->industry)" />
                    <x-input-error :messages="$errors->get('industry')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="website" :value="__('Weboldal')" />
                    <x-text-input id="website" name="website" class="block mt-1 w-full" :value="old('website', $organization->website)" placeholder="https://..." />
                    <x-input-error :messages="$errors->get('website')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('organizations.show', $organization) }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

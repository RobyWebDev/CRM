<x-guest-layout>
    <div class="mb-4 text-fluid-xs text-ink-muted">
        {{ __('Ez az alkalmazás egy védett területe. Kérjük, folytatás előtt erősítsd meg a jelszavad.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Jelszó')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Megerősítés') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

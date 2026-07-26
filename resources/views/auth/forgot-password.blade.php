<x-guest-layout>
    <div class="mb-4 text-fluid-xs text-ink-muted">
        {{ __('Elfelejtetted a jelszavad? Semmi gond. Add meg az e-mail címed, és küldünk egy linket, amellyel új jelszót állíthatsz be.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Jelszó-visszaállító link küldése') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

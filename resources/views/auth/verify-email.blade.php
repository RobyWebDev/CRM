<x-guest-layout>
    <div class="mb-4 text-fluid-xs text-ink-muted">
        {{ __('Köszönjük a regisztrációt! Mielőtt elkezdenéd használni a rendszert, kérjük, erősítsd meg az e-mail címed az imént küldött linkre kattintva. Ha nem kaptad meg a levelet, szívesen küldünk egy újat.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-fluid-xs text-success">
            {{ __('Új megerősítő linket küldtünk a regisztráció során megadott e-mail címre.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Megerősítő e-mail újraküldése') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-fluid-xs text-ink-muted hover:text-ink rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-line-strong focus-visible:ring-offset-page">
                {{ __('Kijelentkezés') }}
            </button>
        </form>
    </div>
</x-guest-layout>

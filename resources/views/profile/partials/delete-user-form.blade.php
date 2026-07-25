@if (in_array($user->role, ['owner', 'admin'], true))
    <section class="space-y-6">
        <header>
            <h2 class="text-fluid-lg font-medium text-ink">
                {{ __('Fiók törlése') }}
            </h2>

            <p class="mt-1 text-fluid-xs text-ink-muted">
                {{ __('A fiók törlése után minden hozzá tartozó adat véglegesen elvész. Ez a lehetőség csak owner/admin szerepkörű felhasználóknak érhető el.') }}
            </p>
        </header>

        <a href="{{ route('profile.export') }}">
            <x-secondary-button type="button">{{ __('Adataid mentése (exportálás)') }}</x-secondary-button>
        </a>

        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >{{ __('Fiók törlése') }}</x-danger-button>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                @csrf
                @method('delete')

                <h2 class="text-fluid-lg font-medium text-ink">
                    {{ __('Biztosan törlöd a fiókot?') }}
                </h2>

                <p class="mt-1 text-fluid-xs text-ink-muted">
                    {{ __('A törlés után minden adat véglegesen elvész. Mielőtt folytatod, javasolt előbb elmenteni az adataidat.') }}
                </p>

                <a href="{{ route('profile.export') }}" class="inline-block mt-3">
                    <x-secondary-button type="button">{{ __('Adatok mentése most (exportálás)') }}</x-secondary-button>
                </a>

                <div class="mt-6">
                    <x-input-label for="password" value="{{ __('Jelszó') }}" class="sr-only" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="{{ __('Jelszó') }}"
                    />

                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Mégse') }}
                    </x-secondary-button>

                    <x-danger-button class="ms-3">
                        {{ __('Fiók törlése') }}
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    </section>
@endif

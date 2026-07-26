<section>
    <header>
        <h2 class="text-fluid-lg font-medium text-ink">
            {{ __('Profil adatai') }}
        </h2>

        <p class="mt-1 text-fluid-xs text-ink-muted">
            {{ __('Frissítsd a fiókod profiladatait és e-mail címét.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Név')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-fluid-xs mt-2 text-ink-soft">
                        {{ __('Az e-mail címed még nincs megerősítve.') }}

                        <button form="send-verification" class="underline text-fluid-xs text-ink-muted hover:text-ink rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-line-strong focus-visible:ring-offset-page">
                            {{ __('Kattints ide a megerősítő e-mail újraküldéséhez.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-fluid-xs text-success">
                            {{ __('Új megerősítő linket küldtünk az e-mail címedre.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Mentés') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-fluid-xs text-ink-muted"
                >{{ __('Mentve.') }}</p>
            @endif
        </div>
    </form>
</section>

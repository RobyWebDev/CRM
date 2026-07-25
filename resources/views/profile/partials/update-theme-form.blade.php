@php
    $account = $user->account;
@endphp

<section>
    <header>
        <h2 class="text-fluid-lg font-medium text-ink">
            {{ __('Megjelenés') }}
        </h2>

        <p class="mt-1 text-fluid-xs text-ink-muted">
            {{ __('A paletta a teljes fiókra vonatkozik, a sötét/világos mód csak a te személyes beállításod.') }}
        </p>
    </header>

    <form method="post" action="{{ route('theme.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="theme_palette" :value="__('Paletta (fiók-szintű)')" />
            <select id="theme_palette" name="theme_palette" class="mt-1 block w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                <option value="forest" @selected($account->theme_palette === 'forest')>{{ __('Forest (zöld)') }}</option>
                <option value="salesforce" @selected($account->theme_palette === 'salesforce')>{{ __('Salesforce-stílus (kék/fehér)') }}</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('theme_palette')" />
        </div>

        <div>
            <x-input-label for="theme_mode" :value="__('Mód (személyes)')" />
            <select id="theme_mode" name="theme_mode" class="mt-1 block w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                <option value="" @selected(is_null($user->theme_mode))>{{ __('Paletta alapértelmezése') }}</option>
                <option value="dark" @selected($user->theme_mode === 'dark')>{{ __('Sötét') }}</option>
                <option value="light" @selected($user->theme_mode === 'light')>{{ __('Világos') }}</option>
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('theme_mode')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Mentés') }}</x-primary-button>

            @if (session('status') === 'theme-updated')
                <p class="text-fluid-xs text-success">{{ __('Mentve.') }}</p>
            @endif
        </div>
    </form>
</section>

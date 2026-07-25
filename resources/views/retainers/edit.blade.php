<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Retainer szerkesztése') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('retainers.update', $retainer) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="title" :value="__('Cím')" />
                    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $retainer->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Leírás')" />
                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('description', $retainer->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="monthly_fee" :value="__('Havi díj (Ft)')" />
                        <x-text-input id="monthly_fee" type="number" step="1" min="0" name="monthly_fee" class="block mt-1 w-full" :value="old('monthly_fee', $retainer->monthly_fee)" />
                        <x-input-error :messages="$errors->get('monthly_fee')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="billing_cycle" :value="__('Számlázási ciklus')" />
                        <select id="billing_cycle" name="billing_cycle" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                            @foreach (['monthly' => 'Havi', 'quarterly' => 'Negyedéves', 'other' => 'Egyéb'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('billing_cycle', $retainer->billing_cycle) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('billing_cycle')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="billing_day" :value="__('Számlázás napja (hónapban)')" />
                        <x-text-input id="billing_day" type="number" min="1" max="28" name="billing_day" class="block mt-1 w-full" :value="old('billing_day', $retainer->billing_day)" />
                        <x-input-error :messages="$errors->get('billing_day')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Állapot')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                            @foreach (['active' => 'Aktív', 'paused' => 'Szüneteltetve', 'ended' => 'Lezárva'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $retainer->status) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('retainers.show', $retainer) }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ trim($lead->first_name.' '.$lead->last_name) }}
            </h2>
            <div class="flex gap-fluid-xs">
                @if ($lead->status !== 'converted')
                    <form method="POST" action="{{ route('leads.convert', $lead) }}" onsubmit="return confirm('{{ __('Konvertálod kontaktá (és esetleg üzletté)?') }}')">
                        @csrf
                        <x-primary-button>{{ __('Konvertálás kontaktá') }}</x-primary-button>
                    </form>
                @else
                    <a href="{{ route('contacts.show', $lead->converted_contact_id) }}">
                        <x-secondary-button type="button">{{ __('Ugrás a kontaktra') }}</x-secondary-button>
                    </a>
                @endif
                <form method="POST" action="{{ route('leads.destroy', $lead) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                    @csrf
                    @method('delete')
                    <x-danger-button>{{ __('Törlés') }}</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'lead-already-converted')
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-warning mb-fluid-sm">
                    {{ __('Ez a lead már konvertálva lett korábban.') }}
                </div>
            @endif

            <form method="POST" action="{{ route('leads.update', $lead) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="first_name" :value="__('Keresztnév')" />
                        <x-text-input id="first_name" name="first_name" class="block mt-1 w-full" :value="old('first_name', $lead->first_name)" required autofocus />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="last_name" :value="__('Vezetéknév')" />
                        <x-text-input id="last_name" name="last_name" class="block mt-1 w-full" :value="old('last_name', $lead->last_name)" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="email" :value="__('E-mail')" />
                        <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $lead->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="phone" :value="__('Telefon')" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $lead->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="company" :value="__('Cég')" />
                        <x-text-input id="company" name="company" class="block mt-1 w-full" :value="old('company', $lead->company)" />
                        <x-input-error :messages="$errors->get('company')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="source" :value="__('Forrás')" />
                        <x-text-input id="source" name="source" class="block mt-1 w-full" :value="old('source', $lead->source)" />
                        <x-input-error :messages="$errors->get('source')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="status" :value="__('Állapot')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                            @foreach (['new' => 'Új', 'contacted' => 'Felvéve kapcsolat', 'qualified' => 'Minősített', 'unqualified' => 'Elutasított'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $lead->status) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="score" :value="__('Lead-pontszám (0-100)')" />
                        <x-text-input id="score" type="number" min="0" max="100" name="score" class="block mt-1 w-full" :value="old('score', $lead->score)" />
                        <x-input-error :messages="$errors->get('score')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="service_type_id" :value="__('Érdeklődik iránta')" />
                    <select id="service_type_id" name="service_type_id" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— nincs megadva —') }}</option>
                        @foreach ($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}" @selected(old('service_type_id', $lead->service_type_id) == $serviceType->id)>{{ $serviceType->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('service_type_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Jegyzet')" />
                    <textarea id="notes" name="notes" rows="4" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('notes', $lead->notes) }}</textarea>
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

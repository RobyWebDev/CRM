<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ $lead->full_name }}
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

            @if ((session('duplicate_leads') && count(session('duplicate_leads')) > 0) || (session('duplicate_contacts') && count(session('duplicate_contacts')) > 0))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-warning mb-fluid-sm">
                    <p class="font-medium">{{ __('Figyelem: hasonló e-maillel/telefonnal már van nyilvántartva:') }}</p>
                    <ul class="list-disc list-inside mt-1">
                        @foreach (session('duplicate_leads', []) as $duplicate)
                            <li>{{ __('Lead') }}: <a href="{{ route('leads.edit', $duplicate['id']) }}" class="underline">{{ $duplicate['name'] }}</a></li>
                        @endforeach
                        @foreach (session('duplicate_contacts', []) as $duplicate)
                            <li>{{ __('Kontakt') }}: <a href="{{ route('contacts.show', $duplicate['id']) }}" class="underline">{{ $duplicate['name'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('leads.update', $lead) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <x-name-fields :first-name="old('first_name', $lead->first_name)" :last-name="old('last_name', $lead->last_name)" />

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

                <div>
                    <x-input-label for="campaign_id" :value="__('Kampány (opcionális)')" />
                    <select id="campaign_id" name="campaign_id" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— nincs —') }}</option>
                        @foreach ($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" @selected(old('campaign_id', $lead->campaign_id) == $campaign->id)>{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('campaign_id')" class="mt-2" />
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
                        <x-input-label for="win_probability" :value="__('Esély a megnyerésre (%)')" />
                        <x-text-input id="win_probability" type="number" min="0" max="100" name="win_probability" class="block mt-1 w-full" :value="old('win_probability', $lead->win_probability)" />
                        <x-input-error :messages="$errors->get('win_probability')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="service_type_id" :value="__('Érdeklődik iránta (szolgáltatás típusa)')" />
                        <select id="service_type_id" name="service_type_id" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                            <option value="">{{ __('— nincs megadva —') }}</option>
                            @foreach ($serviceTypes as $serviceType)
                                <option value="{{ $serviceType->id }}" @selected(old('service_type_id', $lead->service_type_id) == $serviceType->id)>{{ $serviceType->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('service_type_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="project_title" :value="__('Projekt / feladat megnevezése')" />
                        <x-text-input id="project_title" name="project_title" class="block mt-1 w-full" :value="old('project_title', $lead->project_title)" placeholder="{{ __('pl. Facebook-hirdetéskezelés Q3-ra') }}" />
                        <x-input-error :messages="$errors->get('project_title')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="current_status_note" :value="__('Jelenlegi állás — hol tart most a projekt')" />
                    <textarea id="current_status_note" name="current_status_note" rows="2" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('current_status_note', $lead->current_status_note) }}</textarea>
                    <x-input-error :messages="$errors->get('current_status_note')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-fluid-sm">
                    <div class="sm:col-span-2">
                        <x-input-label for="next_step" :value="__('Következő lépés')" />
                        <x-text-input id="next_step" name="next_step" class="block mt-1 w-full" :value="old('next_step', $lead->next_step)" placeholder="{{ __('pl. hívás egyeztetése, ajánlat kiküldése...') }}" />
                        <x-input-error :messages="$errors->get('next_step')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="next_step_due_at" :value="__('Várható időpont')" />
                        <x-text-input id="next_step_due_at" type="date" name="next_step_due_at" class="block mt-1 w-full" :value="old('next_step_due_at', $lead->next_step_due_at?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('next_step_due_at')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="comment" :value="__('Megjegyzés (egyéb infók)')" />
                    <textarea id="comment" name="comment" rows="4" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('comment', $lead->comment) }}</textarea>
                    <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('leads.index') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>

            <div class="bg-surface border border-line rounded-lg p-fluid-md mt-fluid-sm">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Aktivitás') }}</h3>
                <x-activity-timeline :subject="$lead" />
            </div>
        </div>
    </div>
</x-app-layout>

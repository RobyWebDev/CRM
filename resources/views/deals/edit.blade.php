<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Üzlet szerkesztése') }}
            </h2>
            <form method="POST" action="{{ route('deals.destroy', $deal) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                @csrf
                @method('delete')
                <x-danger-button>{{ __('Törlés') }}</x-danger-button>
            </form>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('deals.update', $deal) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="title" :value="__('Cím')" />
                    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $deal->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="pipeline_stage_id" :value="__('Lépés')" />
                    <select id="pipeline_stage_id" name="pipeline_stage_id" required
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}" @selected(old('pipeline_stage_id', $deal->pipeline_stage_id) == $stage->id)>
                                {{ $stage->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('pipeline_stage_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="contact_id" :value="__('Kontakt')" />
                    <select id="contact_id" name="contact_id"
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— nincs —') }}</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected(old('contact_id', $deal->contact_id) == $contact->id)>
                                {{ $contact->full_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('contact_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="value" :value="__('Várható érték (Ft)')" />
                    <x-text-input id="value" name="value" type="number" step="1" min="0" class="block mt-1 w-full" :value="old('value', $deal->value)" />
                    <x-input-error :messages="$errors->get('value')" class="mt-2" />
                </div>

                @if ($deal->status !== 'open')
                    <p class="text-fluid-xs text-ink-muted">
                        {{ __('Állapot') }}: <span class="font-semibold {{ $deal->status === 'won' ? 'text-success' : 'text-danger' }}">{{ $deal->status === 'won' ? __('Nyert') : __('Elveszett') }}</span>
                    </p>
                @endif

                @if ($deal->status === 'lost')
                    <div>
                        <x-input-label for="lost_reason" :value="__('Elvesztés oka (tanulság a jövőre)')" />
                        <textarea id="lost_reason" name="lost_reason" rows="2" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('lost_reason', $deal->lost_reason) }}</textarea>
                        <x-input-error :messages="$errors->get('lost_reason')" class="mt-2" />
                    </div>
                @endif

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('deals.index', ['pipeline' => $deal->pipeline_id]) }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

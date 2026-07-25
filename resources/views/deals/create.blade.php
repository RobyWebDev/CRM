<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Új üzlet') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('deals.store') }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm"
                  x-data="{ pipelineId: '{{ old('pipeline_id', $selectedPipeline?->id) }}' }">
                @csrf

                <div>
                    <x-input-label for="title" :value="__('Cím')" />
                    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title')" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="pipeline_id" :value="__('Pipeline')" />
                    <select id="pipeline_id" name="pipeline_id" x-model="pipelineId" required
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        @foreach ($pipelines as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('pipeline_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="pipeline_stage_id" :value="__('Lépés')" />
                    <select id="pipeline_stage_id" name="pipeline_stage_id" required
                            class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        @foreach ($pipelines as $p)
                            @foreach ($p->stages as $stage)
                                <option value="{{ $stage->id }}" x-show="pipelineId == {{ $p->id }}" @selected(old('pipeline_stage_id') == $stage->id)>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
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
                            <option value="{{ $contact->id }}" @selected(old('contact_id') == $contact->id)>
                                {{ trim($contact->first_name.' '.$contact->last_name) }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('contact_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="value" :value="__('Várható érték (Ft)')" />
                    <x-text-input id="value" name="value" type="number" step="1" min="0" class="block mt-1 w-full" :value="old('value')" />
                    <x-input-error :messages="$errors->get('value')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('deals.index') }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

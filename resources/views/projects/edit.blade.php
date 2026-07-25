<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Projekt szerkesztése') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('projects.update', $project) }}" class="bg-surface border border-line rounded-lg p-fluid-md space-y-fluid-sm">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="title" :value="__('Cím')" />
                    <x-text-input id="title" name="title" class="block mt-1 w-full" :value="old('title', $project->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" :value="__('Leírás')" />
                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">{{ old('description', $project->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="contact_id" :value="__('Kontakt')" />
                    <select id="contact_id" name="contact_id" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        <option value="">{{ __('— nincs —') }}</option>
                        @foreach ($contacts as $contact)
                            <option value="{{ $contact->id }}" @selected(old('contact_id', $project->contact_id) == $contact->id)>{{ $contact->full_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('contact_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="start_date" :value="__('Kezdés')" />
                        <x-text-input id="start_date" type="date" name="start_date" class="block mt-1 w-full" :value="old('start_date', $project->start_date?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="due_date" :value="__('Határidő')" />
                        <x-text-input id="due_date" type="date" name="due_date" class="block mt-1 w-full" :value="old('due_date', $project->due_date?->format('Y-m-d'))" />
                        <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-fluid-sm">
                    <div>
                        <x-input-label for="budget" :value="__('Büdzsé (Ft)')" />
                        <x-text-input id="budget" type="number" step="1" min="0" name="budget" class="block mt-1 w-full" :value="old('budget', $project->budget)" />
                        <x-input-error :messages="$errors->get('budget')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Állapot')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                            @foreach (['active' => 'Aktív', 'on_hold' => 'Szüneteltetve', 'completed' => 'Befejezve', 'cancelled' => 'Lemondva'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="invoice_status" :value="__('Számlázás')" />
                    <select id="invoice_status" name="invoice_status" class="block mt-1 w-full rounded-md border-line-strong bg-sunken text-ink text-fluid-base focus:border-line-strong focus:ring-line-strong">
                        @foreach (['not_issued' => 'Nincs kiállítva', 'issued' => 'Kiállítva', 'paid' => 'Fizetve'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('invoice_status', $project->invoice_status) === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('invoice_status')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end gap-fluid-xs">
                    <a href="{{ route('projects.show', $project) }}"><x-secondary-button type="button">{{ __('Mégse') }}</x-secondary-button></a>
                    <x-primary-button>{{ __('Mentés') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

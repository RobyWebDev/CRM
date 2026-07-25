<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Saját jegyzetek') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <p class="text-ink-muted text-fluid-xs">{{ __('Ezek a jegyzetek csak neked látszanak — nincsenek semmilyen kontakthoz, üzlethez vagy projekthez kötve.') }}</p>

            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'note-created') {{ __('Jegyzet felvéve.') }} @endif
                    @if (session('status') === 'note-updated') {{ __('Jegyzet frissítve.') }} @endif
                    @if (session('status') === 'note-deleted') {{ __('Jegyzet törölve.') }} @endif
                </div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <form method="POST" action="{{ route('personal-notes.store') }}" class="flex gap-2">
                    @csrf
                    <textarea name="body" required rows="2" placeholder="{{ __('Új saját jegyzet...') }}"
                              class="flex-1 text-fluid-base rounded-md border-line-strong bg-sunken text-ink focus:border-line-strong focus:ring-line-strong"></textarea>
                    <x-primary-button type="submit">{{ __('+ Hozzáad') }}</x-primary-button>
                </form>
            </div>

            <div class="space-y-2">
                @forelse ($notes as $note)
                    <div class="bg-surface border border-line rounded-lg p-fluid-sm" x-data="{ editing: false }">
                        <div x-show="! editing">
                            <p class="text-ink text-fluid-base whitespace-pre-line">{{ $note->body }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-ink-muted text-fluid-xs">{{ $note->created_at->format('Y.m.d. H:i') }}</p>
                                <div class="flex gap-fluid-xs">
                                    <button type="button" @click="editing = true" class="text-accent underline text-fluid-xs">{{ __('Szerkesztés') }}</button>
                                    <form method="POST" action="{{ route('personal-notes.destroy', $note) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-danger underline text-fluid-xs">{{ __('Törlés') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <form x-show="editing" method="POST" action="{{ route('personal-notes.update', $note) }}" class="space-y-2">
                            @csrf
                            @method('patch')
                            <textarea name="body" required rows="2"
                                      class="w-full text-fluid-base rounded-md border-line-strong bg-sunken text-ink focus:border-line-strong focus:ring-line-strong">{{ $note->body }}</textarea>
                            <div class="flex gap-fluid-xs">
                                <x-secondary-button type="submit">{{ __('Mentés') }}</x-secondary-button>
                                <x-secondary-button type="button" @click="editing = false">{{ __('Mégse') }}</x-secondary-button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-ink-muted text-fluid-sm italic">{{ __('Még nincs saját jegyzeted.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>

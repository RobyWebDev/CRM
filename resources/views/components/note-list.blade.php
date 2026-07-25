{{-- Újrahasznosítható jegyzet-lista + felvevő űrlap bármely noteable entitáshoz. --}}
@props(['noteable', 'noteableType'])

<div class="space-y-2">
    @forelse ($noteable->notes()->with('user')->latest()->get() as $note)
        <div class="bg-sunken border border-line rounded-md px-3 py-2">
            <p class="text-ink text-fluid-base whitespace-pre-line">{{ $note->body }}</p>
            <p class="text-ink-muted text-fluid-xs mt-1">{{ $note->user?->name }} — {{ $note->created_at->format('Y.m.d. H:i') }}</p>
        </div>
    @empty
        <p class="text-ink-muted text-fluid-xs italic">{{ __('Még nincs jegyzet.') }}</p>
    @endforelse

    <form method="POST" action="{{ route('notes.store') }}" class="flex gap-2 mt-2">
        @csrf
        <input type="hidden" name="noteable_type" value="{{ $noteableType }}">
        <input type="hidden" name="noteable_id" value="{{ $noteable->id }}">
        <textarea name="body" required rows="2" placeholder="{{ __('Új jegyzet...') }}"
                  class="flex-1 text-fluid-base rounded-md border-line-strong bg-sunken text-ink focus:border-line-strong focus:ring-line-strong"></textarea>
        <x-secondary-button type="submit">{{ __('+ Hozzáad') }}</x-secondary-button>
    </form>
</div>

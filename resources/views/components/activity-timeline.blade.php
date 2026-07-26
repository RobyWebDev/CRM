{{-- Aktivitás-idővonal — ki mikor mit módosított egy rekordon (spatie/laravel-activitylog). --}}
@props(['subject'])

<div class="space-y-1">
    @forelse ($subject->activities()->with('causer')->latest()->limit(20)->get() as $activity)
        <p class="text-fluid-xs text-ink-soft border-b border-line pb-1 last:border-0">
            <span class="font-medium text-ink">{{ $activity->causer?->name ?? __('Rendszer') }}</span>
            {{ match ($activity->description) {
                'created' => __('létrehozta'),
                'updated' => __('módosította'),
                'deleted' => __('törölte'),
                default => $activity->description,
            } }}
            @php $changedAttributes = array_keys($activity->changes()->get('attributes', [])); @endphp
            @if ($activity->description === 'updated' && ! empty($changedAttributes))
                <span class="text-ink-muted">({{ implode(', ', $changedAttributes) }})</span>
            @endif
            <span class="text-ink-muted">— {{ $activity->created_at->format('Y.m.d. H:i') }}</span>
        </p>
    @empty
        <p class="text-ink-muted text-fluid-xs italic">{{ __('Még nincs rögzített aktivitás.') }}</p>
    @endforelse
</div>

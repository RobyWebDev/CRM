<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
            {{ __('Audit napló') }}
        </h2>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            <p class="text-ink-muted text-fluid-xs">
                {{ __('Ki, mikor, mit módosított a fiókodban — a rekordonkénti idővonal (kontakt/üzlet/projekt/retainer oldalán) itt egy helyen, összesítve.') }}
            </p>

            <div class="flex gap-fluid-xs flex-wrap">
                <a href="{{ route('activity-log.index') }}"
                   class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $subjectType === '' ? 'bg-accent text-accent-ink' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                    {{ __('Összes') }}
                </a>
                @foreach ($subjectLabels as $class => $label)
                    <a href="{{ route('activity-log.index', ['subject_type' => $class]) }}"
                       class="px-4 py-2 rounded-md text-fluid-xs font-medium transition {{ $subjectType === $class ? 'bg-accent text-accent-ink' : 'bg-surface text-ink-soft hover:bg-surface-hover' }}">
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

            @if ($activities->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Még nincs rögzített aktivitás.') }}
                </div>
            @else
                <div class="bg-surface border border-line rounded-lg p-fluid-md">
                    <ul class="divide-y divide-line">
                        @foreach ($activities as $activity)
                            <li class="py-2 text-fluid-xs text-ink-soft">
                                <span class="font-medium text-ink">{{ $activity->causer?->name ?? __('Rendszer') }}</span>
                                {{ match ($activity->description) {
                                    'created' => __('létrehozta'),
                                    'updated' => __('módosította'),
                                    'deleted' => __('törölte'),
                                    default => $activity->description,
                                } }}
                                @if (isset($subjectLabels[$activity->subject_type]))
                                    <span class="text-ink-muted">({{ __($subjectLabels[$activity->subject_type]) }})</span>
                                @endif
                                @if ($activity->subject && isset($subjectRoutes[$activity->subject_type]))
                                    @php
                                        $subjectLabel = $activity->subject->full_name ?? $activity->subject->title ?? ('#'.$activity->subject_id);
                                    @endphp
                                    <a href="{{ route($subjectRoutes[$activity->subject_type], $activity->subject) }}" class="text-accent underline">
                                        {{ $subjectLabel }}
                                    </a>
                                @endif
                                @php $changedAttributes = array_keys($activity->changes()->get('attributes', [])); @endphp
                                @if ($activity->description === 'updated' && ! empty($changedAttributes))
                                    <span class="text-ink-muted">— {{ implode(', ', $changedAttributes) }}</span>
                                @endif
                                <span class="text-ink-muted">— {{ $activity->created_at->format('Y.m.d. H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>{{ $activities->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

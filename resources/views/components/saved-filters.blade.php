{{-- Mentett szűrők/nézetek egy listaoldalhoz — csak a bejelentkezett felhasználóhoz tartoznak. --}}
@props(['resource', 'indexRoute'])

@php
    $savedFilters = \App\Models\SavedFilter::where('resource', $resource)
        ->where('user_id', auth()->id())
        ->orderBy('name')
        ->get();
    $currentQuery = request()->getQueryString();
@endphp

@if ($savedFilters->isNotEmpty() || $currentQuery)
    <div class="flex items-center gap-2 flex-wrap text-fluid-xs">
        @if ($savedFilters->isNotEmpty())
            <span class="text-ink-muted">{{ __('Mentett szűrők') }}:</span>
            @foreach ($savedFilters as $filter)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-sunken text-ink-soft">
                    <a href="{{ route($indexRoute) }}?{{ $filter->query_string }}" class="hover:underline">{{ $filter->name }}</a>
                    <form method="POST" action="{{ route('saved-filters.destroy', $filter) }}" onsubmit="return confirm('{{ __('Törlöd ezt a mentett szűrőt?') }}')">
                        @csrf
                        @method('delete')
                        <button type="submit" class="text-ink-muted hover:text-danger" aria-label="{{ __('Mentett szűrő törlése') }}">&times;</button>
                    </form>
                </span>
            @endforeach
        @endif

        @if ($currentQuery)
            <form method="POST" action="{{ route('saved-filters.store') }}" class="flex items-center gap-1">
                @csrf
                <input type="hidden" name="resource" value="{{ $resource }}">
                <input type="hidden" name="query_string" value="{{ $currentQuery }}">
                <label for="saved-filter-name-{{ $resource }}" class="sr-only">{{ __('Szűrő neve') }}</label>
                <input type="text" id="saved-filter-name-{{ $resource }}" name="name" required maxlength="100"
                       placeholder="{{ __('pl. Forró leadjeim') }}"
                       class="text-fluid-xs rounded-md border-line-strong bg-sunken text-ink-soft w-36 focus:border-line-strong focus:ring-line-strong">
                <x-secondary-button type="submit">{{ __('Jelenlegi szűrés mentése') }}</x-secondary-button>
            </form>
        @endif
    </div>
@endif

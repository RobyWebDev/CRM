{{--
    Újrahasznosítható teendő-lista + felvevő űrlap bármely taskable entitáshoz
    (Project, Retainer, Contact, Deal, Lead) — lásd adatmodell.md polimorf tasks tábla.
--}}
@props(['taskable', 'taskableType'])

<div class="space-y-2">
    @forelse ($taskable->tasks()->orderBy('status')->orderBy('due_date')->get() as $task)
        <div class="flex items-center justify-between gap-fluid-xs bg-sunken border border-line rounded-md px-3 py-2">
            <div class="flex items-center gap-2 min-w-0">
                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('patch')
                    <button type="submit" class="w-5 h-5 rounded border border-line-strong flex items-center justify-center {{ $task->status === 'done' ? 'bg-success' : 'bg-transparent' }}" aria-label="{{ __('Kész / nem kész') }}">
                        @if ($task->status === 'done')
                            <span class="text-page text-fluid-xs leading-none">&#10003;</span>
                        @endif
                    </button>
                </form>
                <div class="min-w-0">
                    <p class="text-fluid-base {{ $task->status === 'done' ? 'line-through text-ink-muted' : 'text-ink' }} truncate">{{ $task->title }}</p>
                    @if ($task->due_date)
                        <p class="text-fluid-xs text-ink-muted">{{ __('Határidő') }}: {{ $task->due_date->format('Y.m.d.') }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                @csrf
                @method('delete')
                <button type="submit" class="text-danger text-fluid-xs underline">{{ __('Törlés') }}</button>
            </form>
        </div>
    @empty
        <p class="text-ink-muted text-fluid-xs italic">{{ __('Még nincs teendő felvéve.') }}</p>
    @endforelse

    <form method="POST" action="{{ route('tasks.store') }}" class="flex gap-2 mt-2">
        @csrf
        <input type="hidden" name="taskable_type" value="{{ $taskableType }}">
        <input type="hidden" name="taskable_id" value="{{ $taskable->id }}">
        <input type="text" name="title" required placeholder="{{ __('Új teendő...') }}"
               class="flex-1 text-fluid-base rounded-md border-line-strong bg-sunken text-ink focus:border-line-strong focus:ring-line-strong" />
        <input type="date" name="due_date"
               class="text-fluid-base rounded-md border-line-strong bg-sunken text-ink focus:border-line-strong focus:ring-line-strong" />
        <x-secondary-button type="submit">{{ __('+ Hozzáad') }}</x-secondary-button>
    </form>
</div>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ $retainer->title }}
            </h2>
            <div class="flex gap-fluid-xs">
                <a href="{{ route('retainers.edit', $retainer) }}"><x-secondary-button type="button">{{ __('Szerkesztés') }}</x-secondary-button></a>
                <form method="POST" action="{{ route('retainers.destroy', $retainer) }}" onsubmit="return confirm('{{ __('Biztosan törlöd?') }}')">
                    @csrf
                    @method('delete')
                    <x-danger-button>{{ __('Törlés') }}</x-danger-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'task-created') {{ __('Teendő felvéve.') }} @endif
                    @if (session('status') === 'task-recurred') {{ __('Teendő kész, a következő előfordulás automatikusan létrejött.') }} @endif
                </div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                @if ($retainer->deal)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Eredeti üzlet') }}:</span> <a href="{{ route('deals.edit', $retainer->deal) }}" class="text-accent underline">{{ $retainer->deal->title }}</a></p>
                @endif
                @if ($retainer->contact)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Kontakt') }}:</span> <a href="{{ route('contacts.show', $retainer->contact) }}" class="text-accent underline">{{ $retainer->contact->full_name }}</a></p>
                @endif
                @if ($retainer->serviceType)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Szolgáltatás') }}:</span> <span class="text-ink">{{ $retainer->serviceType->name }}</span></p>
                @endif
                @if ($retainer->monthly_fee)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Havi díj') }}:</span> <span class="text-ink">{{ number_format($retainer->monthly_fee, 0, ',', ' ') }} Ft</span></p>
                @endif
                <p><span class="text-ink-muted text-fluid-xs">{{ __('Számlázási ciklus') }}:</span> <span class="text-ink">{{ __(match($retainer->billing_cycle) { 'monthly' => 'havi', 'quarterly' => 'negyedéves', default => 'egyéb' }) }}</span></p>
                <p><span class="text-ink-muted text-fluid-xs">{{ __('Állapot') }}:</span> <span class="text-ink font-semibold">{{ __(match ($retainer->status) { 'active' => 'Aktív', 'paused' => 'Szüneteltetve', 'ended' => 'Lezárva', default => $retainer->status }) }}</span></p>
                @if ($retainer->started_at)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Kezdés') }}:</span> <span class="text-ink">{{ $retainer->started_at->format('Y.m.d.') }}</span></p>
                @endif
                @if ($retainer->description)
                    <p class="text-ink-soft whitespace-pre-line">{{ $retainer->description }}</p>
                @endif
            </div>

            @if ($retainer->invoices->isNotEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-md">
                    <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Számlázási periódusok') }}</h3>
                    <div class="space-y-2">
                        @foreach ($retainer->invoices as $invoice)
                            <div class="flex items-center justify-between bg-sunken border border-line rounded-md px-3 py-2">
                                <span class="text-ink text-fluid-base">{{ $invoice->period_start->format('Y.m.d.') }} – {{ $invoice->period_end->format('Y.m.d.') }}</span>
                                <span class="text-fluid-xs px-2 py-0.5 rounded bg-surface text-ink-soft">{{ __(match($invoice->invoice_status) { 'not_issued' => 'Nincs kiállítva', 'issued' => 'Kiállítva', 'paid' => 'Fizetve', default => $invoice->invoice_status }) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Teendők') }}</h3>
                <x-task-list :taskable="$retainer" taskable-type="retainer" />
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Jegyzetek') }}</h3>
                <x-note-list :noteable="$retainer" noteable-type="retainer" />
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Aktivitás') }}</h3>
                <x-activity-timeline :subject="$retainer" />
            </div>

            <a href="{{ route('retainers.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a listához') }}</a>
        </div>
    </div>
</x-app-layout>

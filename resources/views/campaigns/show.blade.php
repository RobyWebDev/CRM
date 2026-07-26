@php
    $wonDeals = $campaign->deals->where('status', 'won');
    $openDeals = $campaign->deals->where('status', 'open');
    $wonValue = $wonDeals->sum('value');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ $campaign->name }}
            </h2>
            <a href="{{ route('campaigns.edit', $campaign) }}">
                <x-secondary-button type="button">{{ __('Szerkesztés') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status') === 'campaign-updated')
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">{{ __('Kampány frissítve.') }}</div>
            @endif

            <div class="bg-surface border border-line rounded-lg p-fluid-md space-y-2">
                @if ($campaign->type)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Típus') }}:</span> <span class="text-ink">{{ $campaign->type }}</span></p>
                @endif
                @if ($campaign->started_at)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Indulás') }}:</span> <span class="text-ink">{{ $campaign->started_at->format('Y.m.d.') }}</span></p>
                @endif
                @if ($campaign->cost)
                    <p><span class="text-ink-muted text-fluid-xs">{{ __('Költség') }}:</span> <span class="text-ink">{{ number_format($campaign->cost, 0, ',', ' ') }} Ft</span></p>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-fluid-sm">
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-center">
                    <p class="text-fluid-lg font-semibold text-ink">{{ $campaign->leads->count() }}</p>
                    <p class="text-fluid-xs text-ink-muted">{{ __('Lead') }}</p>
                </div>
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-center">
                    <p class="text-fluid-lg font-semibold text-ink">{{ $openDeals->count() }}</p>
                    <p class="text-fluid-xs text-ink-muted">{{ __('Nyitott üzlet') }}</p>
                </div>
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-center">
                    <p class="text-fluid-lg font-semibold text-success">{{ $wonDeals->count() }}</p>
                    <p class="text-fluid-xs text-ink-muted">{{ __('Nyert üzlet') }}</p>
                </div>
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-center">
                    <p class="text-fluid-lg font-semibold text-success">{{ number_format($wonValue, 0, ',', ' ') }} Ft</p>
                    <p class="text-fluid-xs text-ink-muted">{{ __('Nyert bevétel') }}</p>
                </div>
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Leadek ebből a kampányból') }}</h3>
                @if ($campaign->leads->isEmpty())
                    <p class="text-ink-muted text-fluid-xs">{{ __('Nincs még lead ebből a kampányból.') }}</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($campaign->leads as $lead)
                            <li class="py-2">
                                <a href="{{ route('leads.edit', $lead) }}" class="text-accent underline text-fluid-sm">{{ $lead->full_name }}</a>
                                <span class="text-fluid-xs text-ink-muted">— {{ $lead->created_at->format('Y.m.d.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-surface border border-line rounded-lg p-fluid-md">
                <h3 class="font-semibold text-fluid-lg text-ink mb-fluid-xs">{{ __('Üzletek ebből a kampányból') }}</h3>
                @if ($campaign->deals->isEmpty())
                    <p class="text-ink-muted text-fluid-xs">{{ __('Nincs még üzlet ebből a kampányból.') }}</p>
                @else
                    <ul class="divide-y divide-line">
                        @foreach ($campaign->deals as $deal)
                            <li class="py-2 flex items-center justify-between gap-2 flex-wrap">
                                <div>
                                    <a href="{{ route('deals.edit', $deal) }}" class="text-accent underline text-fluid-sm">{{ $deal->title }}</a>
                                    @if ($deal->contact)
                                        <span class="text-fluid-xs text-ink-muted">— {{ $deal->contact->full_name }}</span>
                                    @endif
                                </div>
                                <span class="text-fluid-xs font-medium {{ $deal->status === 'won' ? 'text-success' : ($deal->status === 'lost' ? 'text-danger' : 'text-ink-soft') }}">
                                    {{ match ($deal->status) { 'won' => __('Nyert'), 'lost' => __('Elveszett'), default => __('Nyitott') } }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <a href="{{ route('campaigns.index') }}" class="text-accent underline text-fluid-xs">{{ __('← Vissza a listához') }}</a>
        </div>
    </div>
</x-app-layout>

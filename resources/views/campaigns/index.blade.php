<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-fluid-sm flex-wrap">
            <h2 class="font-semibold text-fluid-xl text-ink leading-tight">
                {{ __('Kampányok') }}
            </h2>
            <a href="{{ route('campaigns.create') }}">
                <x-primary-button>{{ __('+ Új kampány') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-fluid-lg">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-fluid-md">
            @if (session('status'))
                <div class="bg-surface border border-line rounded-lg p-fluid-sm text-success">
                    @if (session('status') === 'campaign-created') {{ __('Kampány létrehozva.') }} @endif
                    @if (session('status') === 'campaign-updated') {{ __('Kampány frissítve.') }} @endif
                    @if (session('status') === 'campaign-deleted') {{ __('Kampány törölve.') }} @endif
                </div>
            @endif

            <p class="text-ink-muted text-fluid-xs">
                {{ __('Melyik hirdetésed/csatornád térül meg valójában? A leadek/üzletek forrás-mezője (source) mellett itt strukturáltan is rögzítheted a kampányaidat, kampányonkénti riporttal.') }}
            </p>

            @if ($campaigns->isEmpty())
                <div class="bg-surface border border-line rounded-lg p-fluid-lg text-center text-ink-muted">
                    {{ __('Még nincs egy kampány sem.') }}
                    <a href="{{ route('campaigns.create') }}" class="text-accent underline">{{ __('Vedd fel az elsőt.') }}</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-fluid-sm">
                    @foreach ($campaigns as $campaign)
                        <a href="{{ route('campaigns.show', $campaign) }}" class="bg-surface hover:bg-surface-hover border border-line rounded-lg p-fluid-sm transition">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-ink text-fluid-base">{{ $campaign->name }}</p>
                                @if ($campaign->started_at)
                                    <span class="text-fluid-xs text-ink-muted">{{ $campaign->started_at->format('Y.m.d.') }}</span>
                                @endif
                            </div>
                            @if ($campaign->type)
                                <p class="text-ink-muted text-fluid-xs">{{ $campaign->type }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 mt-2 text-fluid-xs">
                                <span class="text-ink-soft">{{ __('Leadek') }}: <span class="font-semibold text-ink">{{ $campaign->leads_count }}</span></span>
                                <span class="text-ink-soft">{{ __('Nyert üzletek') }}: <span class="font-semibold text-success">{{ $campaign->won_deals_count }}</span></span>
                                @if ($campaign->cost)
                                    <span class="text-ink-soft">{{ __('Költség') }}: <span class="font-semibold text-ink">{{ number_format($campaign->cost, 0, ',', ' ') }} Ft</span></span>
                                @endif
                            </div>
                            @if ($campaign->won_deals_value)
                                <p class="text-fluid-xs mt-1 text-success font-medium">
                                    {{ __('Nyert bevétel') }}: {{ number_format($campaign->won_deals_value, 0, ',', ' ') }} Ft
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

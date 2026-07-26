<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Salesforce Lead Source/Campaign Influence minta egyszerűsítve — lásd
 * docs/ugyfelszerzes-terv.md 3.2. pont. A cél: melyik hirdetés/csatorna
 * térül meg ténylegesen, nem csak a leadek szabad szöveges forrás-mezője.
 */
class CampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = Campaign::query()
            ->withCount('leads')
            ->withCount(['deals as won_deals_count' => fn ($query) => $query->where('status', 'won')])
            ->withSum(['deals as won_deals_value' => fn ($query) => $query->where('status', 'won')], 'value')
            ->orderByDesc('started_at')
            ->orderBy('name')
            ->get();

        return view('campaigns.index', ['campaigns' => $campaigns]);
    }

    public function create(): View
    {
        return view('campaigns.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $campaign = Campaign::create($data);

        return redirect()->route('campaigns.show', $campaign)->with('status', 'campaign-created');
    }

    public function show(Campaign $campaign): View
    {
        $campaign->load(['leads' => fn ($query) => $query->latest(), 'deals' => fn ($query) => $query->with('contact')->latest()]);

        return view('campaigns.show', ['campaign' => $campaign]);
    }

    public function edit(Campaign $campaign): View
    {
        return view('campaigns.edit', ['campaign' => $campaign]);
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $campaign->update($data);

        return redirect()->route('campaigns.show', $campaign)->with('status', 'campaign-updated');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('status', 'campaign-deleted');
    }
}

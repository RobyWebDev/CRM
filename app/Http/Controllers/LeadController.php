<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\ServiceType;
use App\Support\DescriptionChain;
use App\Support\DuplicateFinder;
use App\Support\SelectOrCreate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = Lead::query()
            ->with('serviceType')
            ->when($request->string('status')->isNotEmpty() && $request->string('status') !== 'all', function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            }, function ($query) {
                // alapból a még nyitott (nem konvertált, nem elutasított) leadeket mutatjuk
                $query->whereNotIn('status', ['converted', 'unqualified']);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('leads.index', ['leads' => $leads, 'status' => $request->string('status')->toString()]);
    }

    public function create(): View
    {
        return view('leads.create', [
            'serviceTypes' => ServiceType::orderBy('name')->get(),
            'campaigns' => Campaign::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->resolveCampaignId($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'project_title' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'current_status_note' => ['nullable', 'string'],
            'next_step' => ['nullable', 'string'],
            'next_step_due_at' => ['nullable', 'date'],
            'win_probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string'],
        ]);

        $lead = Lead::create($data + ['status' => 'new']);

        // Nem blokkoló duplikátum-jelzés (CRM best practice) — mind a meglévő leadek,
        // mind a már kontakttá vált emberek között keresünk hasonlót e-mail/telefon
        // alapján, hogy ne vegyünk fel kétszer ugyanazt az érdeklődőt.
        $duplicateLeads = DuplicateFinder::find(Lead::class, $lead->email, $lead->phone, $lead->id);
        $duplicateContacts = DuplicateFinder::find(Contact::class, $lead->email, $lead->phone);

        return redirect()->route('leads.edit', $lead)
            ->with('status', 'lead-created')
            ->with('duplicate_leads', $duplicateLeads->map(fn ($l) => ['id' => $l->id, 'name' => $l->full_name]))
            ->with('duplicate_contacts', $duplicateContacts->map(fn ($c) => ['id' => $c->id, 'name' => $c->full_name]));
    }

    public function edit(Lead $lead): View
    {
        return view('leads.edit', [
            'lead' => $lead,
            'serviceTypes' => ServiceType::orderBy('name')->get(),
            'campaigns' => Campaign::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->resolveCampaignId($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'project_title' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'status' => ['required', 'in:new,contacted,qualified,unqualified'],
            'current_status_note' => ['nullable', 'string'],
            'next_step' => ['nullable', 'string'],
            'next_step_due_at' => ['nullable', 'date'],
            'win_probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'comment' => ['nullable', 'string'],
        ]);

        $lead->update($data);

        // Önállóan felismert hiányosság (2026-07-26): a duplikátum-jelzés eddig csak
        // felvételkor futott, szerkesztésnél nem — lásd ugyanezt a javítást a Contactnál.
        $duplicateLeads = DuplicateFinder::find(Lead::class, $lead->email, $lead->phone, $lead->id);
        $duplicateContacts = DuplicateFinder::find(Contact::class, $lead->email, $lead->phone);

        return redirect()->route('leads.edit', $lead)
            ->with('status', 'lead-updated')
            ->with('duplicate_leads', $duplicateLeads->map(fn ($l) => ['id' => $l->id, 'name' => $l->full_name]))
            ->with('duplicate_contacts', $duplicateContacts->map(fn ($c) => ['id' => $c->id, 'name' => $c->full_name]));
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('leads.index')->with('status', 'lead-deleted');
    }

    /**
     * CRM best practice: a lead "konvertálása" Contactot hoz létre belőle (+ opcionálisan
     * Dealt is az adott szolgáltatás alapértelmezett pipeline-jának első lépésén), a
     * Salesforce Lead→Contact/Opportunity konverziójának egyszerűsített megfelelője.
     */
    public function convert(Lead $lead): RedirectResponse
    {
        if ($lead->status === 'converted') {
            return back()->with('status', 'lead-already-converted');
        }

        // Önállóan felismert hiba (2026-07-26): a lead szabad szöveges `company` mezője
        // eddig konvertáláskor egyszerűen ELVESZETT — sosem lett belőle Szervezet-kapcsolat
        // a Contacton. Most, hogy már van Organization-kezelőfelület, ugyanazzal a
        // kis/nagybetűtől független kereséssel kötjük/hozzuk létre, mint a "+ Új
        // szervezet..." gyors-létrehozásnál (App\Support\SelectOrCreate).
        $organizationId = null;
        if (trim((string) $lead->company) !== '') {
            $organizationId = SelectOrCreate::firstOrCreateByName(Organization::class, trim($lead->company))->id;
        }

        $contact = Contact::create([
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source' => $lead->source,
            'organization_id' => $organizationId,
        ]);

        $deal = null;

        if ($lead->service_type_id) {
            $pipeline = $lead->serviceType->pipelines()->where('is_default', true)->first();
            $firstStage = $pipeline?->stages()->orderBy('sort_order')->first();

            if ($pipeline && $firstStage) {
                // A leadnél rögzített projekt-részletek (project_title, jelenlegi állás,
                // megjegyzés) átöröklődnek a deal leírásába, hogy a scope ne vesszen el
                // a konvertáláskor — lásd crm_projekt.md, "leírás végigfut az egész
                // életúton" elv (2026-07-26, Rob kérése). A fázis + pontos időpont a
                // szövegben is jelölve van (DescriptionChain), hogy utólag látszódjon,
                // melyik rész honnan, mikor került be.
                $leadContent = implode("\n\n", array_filter([
                    $lead->project_title,
                    $lead->current_status_note,
                    $lead->comment,
                ]));

                $deal = Deal::create([
                    'pipeline_id' => $pipeline->id,
                    'pipeline_stage_id' => $firstStage->id,
                    'contact_id' => $contact->id,
                    'campaign_id' => $lead->campaign_id,
                    'title' => $lead->full_name.' — '.$lead->serviceType->name,
                    'description' => DescriptionChain::appendPhaseEntry(null, 'Lead', $leadContent !== '' ? $leadContent : null),
                    'status' => 'open',
                ]);
            }
        }

        $lead->update([
            'status' => 'converted',
            'converted_at' => now(),
            'converted_contact_id' => $contact->id,
            'converted_deal_id' => $deal?->id,
        ]);

        return redirect()->route('contacts.show', $contact)->with('status', 'lead-converted');
    }

    /**
     * "+ Új kampány..." feloldása — ha a felhasználó ezt választotta a lenyíló
     * listában, a beírt névvel VALÓDI Campaign-rekord jön létre (nem szabad
     * szöveges duplikátum), lásd App\Support\SelectOrCreate (Rob kérése, 2026-07-26).
     */
    private function resolveCampaignId(Request $request): void
    {
        $request->merge([
            'campaign_id' => SelectOrCreate::resolveId(Campaign::class, $request->input('campaign_id'), $request->input('new_campaign_name')),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\ServiceType;
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
        return view('leads.create', ['serviceTypes' => ServiceType::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead = Lead::create($data + ['status' => 'new']);

        return redirect()->route('leads.edit', $lead)->with('status', 'lead-created');
    }

    public function edit(Lead $lead): View
    {
        return view('leads.edit', [
            'lead' => $lead,
            'serviceTypes' => ServiceType::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'status' => ['required', 'in:new,contacted,qualified,unqualified'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update($data);

        return redirect()->route('leads.edit', $lead)->with('status', 'lead-updated');
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

        $contact = Contact::create([
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'source' => $lead->source,
        ]);

        $deal = null;

        if ($lead->service_type_id) {
            $pipeline = $lead->serviceType->pipelines()->where('is_default', true)->first();
            $firstStage = $pipeline?->stages()->orderBy('sort_order')->first();

            if ($pipeline && $firstStage) {
                $deal = Deal::create([
                    'pipeline_id' => $pipeline->id,
                    'pipeline_stage_id' => $firstStage->id,
                    'contact_id' => $contact->id,
                    'title' => trim($lead->first_name.' '.$lead->last_name).' — '.$lead->serviceType->name,
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Project;
use App\Models\Retainer;
use App\Support\CustomFieldFormHelper;
use App\Support\DescriptionChain;
use App\Support\SelectOrCreate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealController extends Controller
{
    public function index(Request $request): View
    {
        $pipelines = Pipeline::with('stages')->orderBy('sort_order')->orderBy('name')->get();

        $pipeline = $request->integer('pipeline')
            ? $pipelines->firstWhere('id', $request->integer('pipeline'))
            : $pipelines->first();

        $isBoard = $request->string('view')->toString() === 'board';
        $template = $isBoard ? 'deals.board' : 'deals.index';

        if (! $pipeline) {
            return view($template, ['pipelines' => $pipelines, 'pipeline' => null]);
        }

        // Minden dealt mutatunk a jelenlegi lépésén, státusztól függetlenül — a "won"/"lost"
        // dealek a záró lépésben maradnak látható, nem tűnnek el a nézetből.
        $pipeline->load(['stages' => function ($query) {
            $query->orderBy('sort_order')->with(['deals' => function ($dealQuery) {
                $dealQuery->with('contact', 'organization')->latest();
            }]);
        }]);

        // Súlyozott (forecast) érték — nyitott dealek értéke a stage-hez rendelt valószínűséggel
        // szorozva (CRM best practice, pl. Pipedrive/Salesforce forecast-nézete). A `probability`
        // mező már a séma része volt, eddig csak nem használtuk.
        $openValue = 0;
        $weightedValue = 0;
        foreach ($pipeline->stages as $stage) {
            foreach ($stage->deals as $deal) {
                if ($deal->status === 'open') {
                    $openValue += (float) $deal->value;
                    $weightedValue += (float) $deal->value * (($stage->probability ?? 50) / 100);
                }
            }
        }

        return view($template, [
            'pipelines' => $pipelines,
            'pipeline' => $pipeline,
            'openValue' => $openValue,
            'weightedValue' => $weightedValue,
        ]);
    }

    public function create(Request $request): View
    {
        $pipelines = Pipeline::with('stages')->orderBy('sort_order')->orderBy('name')->get();
        $selectedPipeline = $request->integer('pipeline')
            ? $pipelines->firstWhere('id', $request->integer('pipeline'))
            : $pipelines->first();

        return view('deals.create', [
            'pipelines' => $pipelines,
            'selectedPipeline' => $selectedPipeline,
            'contacts' => Contact::orderBy('first_name')->get(),
            'campaigns' => Campaign::orderBy('name')->get(),
            'serviceTypeId' => $selectedPipeline?->service_type_id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->resolveCampaignId($request);
        $serviceTypeId = Pipeline::find($request->input('pipeline_id'))?->service_type_id;

        $data = $request->validate([
            'pipeline_id' => ['required', 'exists:pipelines,id'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ] + CustomFieldFormHelper::validationRules('deal', $serviceTypeId));

        $deal = Deal::create($data + [
            'status' => 'open',
            'stage_entered_at' => now(),
            'organization_id' => $this->deriveOrganizationId($data['contact_id'] ?? null),
        ]);

        // Ritka eset, de a form elméletileg engedi rögtön egy "won" lépésre felvenni a dealt.
        $this->applyStageChange($deal, $deal->pipeline_stage_id);
        $deal->save();
        $this->maybeCreateProjectOrRetainer($deal);

        return redirect()->route('deals.index', ['pipeline' => $deal->pipeline_id])->with('status', 'deal-created');
    }

    public function edit(Deal $deal): View
    {
        return view('deals.edit', [
            'deal' => $deal,
            'contacts' => Contact::orderBy('first_name')->get(),
            'campaigns' => Campaign::orderBy('name')->get(),
            'stages' => $deal->pipeline->stages()->orderBy('sort_order')->get(),
            'serviceTypeId' => $deal->pipeline->service_type_id,
        ]);
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $this->resolveCampaignId($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'campaign_id' => ['nullable', 'exists:campaigns,id'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'lost_reason' => ['nullable', 'string', 'max:2000'],
        ] + CustomFieldFormHelper::validationRules('deal', $deal->pipeline->service_type_id));

        $data['organization_id'] = $this->deriveOrganizationId($data['contact_id'] ?? null);

        $this->applyStageChange($deal, (int) $data['pipeline_stage_id']);
        $deal->update($data);
        $this->maybeCreateProjectOrRetainer($deal);

        return redirect()->route('deals.index', ['pipeline' => $deal->pipeline_id])->with('status', 'deal-updated');
    }

    /**
     * Egy deal átmozgatása egy másik pipeline-lépésre (drag-and-drop VAGY az
     * akadálymentes "mozgatás" select mindkettő ezt hívja, lásd WCAG 2.5.7).
     */
    public function move(Request $request, Deal $deal): RedirectResponse
    {
        $data = $request->validate([
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
        ]);

        $this->applyStageChange($deal, (int) $data['pipeline_stage_id']);
        $deal->save();
        $this->maybeCreateProjectOrRetainer($deal);

        return back()->with('status', 'deal-moved');
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $pipelineId = $deal->pipeline_id;
        $deal->delete();

        return redirect()->route('deals.index', ['pipeline' => $pipelineId])->with('status', 'deal-deleted');
    }

    /**
     * A pipeline_stage_id váltás automatikusan won/lost állapotba is állítja a dealt,
     * ha a cél-lépés is_won_stage/is_lost_stage — lásd architektura.md 5. pont.
     * A stage_entered_at is frissül, ha ténylegesen új lépésre kerül (napok-a-lépésben számításhoz).
     */
    private function applyStageChange(Deal $deal, int $pipelineStageId): void
    {
        $stage = $deal->pipeline->stages()->findOrFail($pipelineStageId);

        if ($deal->pipeline_stage_id !== $stage->id) {
            $deal->stage_entered_at = now();
        }

        $deal->pipeline_stage_id = $stage->id;

        if ($stage->is_won_stage) {
            $deal->status = 'won';
            $deal->closed_at = now();
        } elseif ($stage->is_lost_stage) {
            $deal->status = 'lost';
            $deal->closed_at = now();
        } else {
            $deal->status = 'open';
            $deal->closed_at = null;
        }
    }

    /**
     * Amint egy deal "won" lesz, a pipeline `won_creates` beállítása alapján automatikusan
     * létrehoz belőle egy egyszeri Projectet VAGY egy ismétlődő Retainert (sosem mindkettőt,
     * és csak egyszer — ha a deal már rendelkezik project/retainer kapcsolattal, kihagyja).
     * Lásd architektura.md 5. pont, crm_projekt.md 7. szekció (retainer-döntés).
     */
    private function maybeCreateProjectOrRetainer(Deal $deal): void
    {
        if ($deal->status !== 'won') {
            return;
        }

        if ($deal->project()->exists() || $deal->retainer()->exists()) {
            return;
        }

        $attributes = [
            'deal_id' => $deal->id,
            'contact_id' => $deal->contact_id,
            'organization_id' => $deal->organization_id,
            'service_type_id' => $deal->pipeline->service_type_id,
            'owner_user_id' => $deal->owner_user_id,
            'title' => $deal->title,
            'status' => 'active',
        ];

        // A leírás-lánc fázis + pontos időpont jelöléssel folytatódik (DescriptionChain) —
        // lásd crm_projekt.md, "leírás végigfut az egész életúton" elv (2026-07-26).
        match ($deal->pipeline->won_creates) {
            'retainer' => Retainer::create($attributes + [
                'description' => DescriptionChain::appendPhaseEntry($deal->description, 'Retainerré vált'),
                'monthly_fee' => $deal->value,
                'started_at' => now()->toDateString(),
            ]),
            'project' => Project::create($attributes + [
                'description' => DescriptionChain::appendPhaseEntry($deal->description, 'Projektté vált'),
                'budget' => $deal->value,
                'start_date' => now()->toDateString(),
            ]),
            default => null,
        };
    }

    /**
     * Önállóan felismert hiba (2026-07-26): a deals.organization_id oszlop a UI-ban
     * SOHA nem lett kitöltve (nem volt hozzá form-mező), ezért mindig NULL maradt —
     * és ez a NULL öröklődött tovább a belőle létrejövő Project/Retainer rekordba is
     * (`maybeCreateProjectOrRetainer`). Best practice (Salesforce Account-öröklés
     * mintája): a Deal automatikusan átveszi a kiválasztott kontakt szervezetét.
     */
    private function deriveOrganizationId(?int $contactId): ?int
    {
        return $contactId ? Contact::find($contactId)?->organization_id : null;
    }

    /**
     * "+ Új kampány..." feloldása — lásd LeadController::resolveCampaignId() ugyanígy.
     */
    private function resolveCampaignId(Request $request): void
    {
        $request->merge([
            'campaign_id' => SelectOrCreate::resolveId(Campaign::class, $request->input('campaign_id'), $request->input('new_campaign_name')),
        ]);
    }
}

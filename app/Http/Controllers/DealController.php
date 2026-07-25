<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
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

        // Minden dealt mutatunk a jelenlegi lépésén, státusztól függetlenül — a "won"/"lost"
        // dealek a záró lépésben maradnak látható, nem tűnnek el a kanban tábláról.
        $pipeline?->load(['stages' => function ($query) {
            $query->orderBy('sort_order')->with(['deals' => function ($dealQuery) {
                $dealQuery->with('contact', 'organization')->latest();
            }]);
        }]);

        return view('deals.index', [
            'pipelines' => $pipelines,
            'pipeline' => $pipeline,
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pipeline_id' => ['required', 'exists:pipelines,id'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'title' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $deal = Deal::create($data + ['status' => 'open']);

        return redirect()->route('deals.index', ['pipeline' => $deal->pipeline_id])->with('status', 'deal-created');
    }

    public function edit(Deal $deal): View
    {
        return view('deals.edit', [
            'deal' => $deal,
            'contacts' => Contact::orderBy('first_name')->get(),
            'stages' => $deal->pipeline->stages()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
        ]);

        $this->applyStageChange($deal, (int) $data['pipeline_stage_id']);
        $deal->update($data);

        return redirect()->route('deals.index', ['pipeline' => $deal->pipeline_id])->with('status', 'deal-updated');
    }

    /**
     * Egy deal átmozgatása egy másik pipeline-lépésre (kanban drag-and-drop VAGY
     * az akadálymentes "mozgatás" select mindkettő ezt hívja, lásd WCAG 2.5.7).
     */
    public function move(Request $request, Deal $deal): RedirectResponse
    {
        $data = $request->validate([
            'pipeline_stage_id' => ['required', 'exists:pipeline_stages,id'],
        ]);

        $this->applyStageChange($deal, (int) $data['pipeline_stage_id']);
        $deal->save();

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
     */
    private function applyStageChange(Deal $deal, int $pipelineStageId): void
    {
        $stage = $deal->pipeline->stages()->findOrFail($pipelineStageId);

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
}

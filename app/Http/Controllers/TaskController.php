<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Teendők — polimorf kapcsolattal bármihez köthetők (Project, Contact, Deal, Lead, Retainer),
 * lásd adatmodell.md. Ez a kontroller nem rendelkezik saját index/show nézettel — a szülő
 * entitás (pl. Project) show-oldalán jelennek meg beágyazva, ott is vehetők fel/zárhatók le.
 */
class TaskController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'taskable_type' => ['required', 'string'],
            'taskable_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $modelClass = match ($data['taskable_type']) {
            'project' => \App\Models\Project::class,
            'contact' => \App\Models\Contact::class,
            'deal' => \App\Models\Deal::class,
            'lead' => \App\Models\Lead::class,
            'retainer' => \App\Models\Retainer::class,
            default => abort(422, 'Ismeretlen taskable_type'),
        };

        $taskable = $modelClass::findOrFail($data['taskable_id']);

        Task::create([
            'taskable_type' => $modelClass,
            'taskable_id' => $taskable->id,
            'title' => $data['title'],
            'due_date' => $data['due_date'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('status', 'task-created');
    }

    public function toggle(Task $task): RedirectResponse
    {
        if ($task->status === 'done') {
            $task->update(['status' => 'open', 'completed_at' => null]);
        } else {
            $task->update(['status' => 'done', 'completed_at' => now()]);
        }

        return back()->with('status', 'task-updated');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('status', 'task-deleted');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $projects = Project::query()
            ->with('contact', 'serviceType')
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status), fn ($q) => $q->whereIn('status', ['active', 'on_hold']))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('projects.index', ['projects' => $projects, 'status' => $status]);
    }

    public function show(Project $project): View
    {
        $project->load('contact', 'organization', 'serviceType', 'deal', 'tasks', 'notes.user');

        return view('projects.show', ['project' => $project]);
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', [
            'project' => $project,
            'contacts' => Contact::orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'status' => ['required', 'in:active,on_hold,completed,cancelled'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'invoice_status' => ['required', 'in:not_issued,issued,paid'],
        ]);

        $project->update($data);

        return redirect()->route('projects.show', $project)->with('status', 'project-updated');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')->with('status', 'project-deleted');
    }
}

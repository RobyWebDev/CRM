<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Jegyzetek — polimorf kapcsolattal bármihez köthetők (Project, Contact, Deal, Lead, Retainer).
 * Nincs saját nézete: a szülő entitás show-oldalán jelenik meg beágyazva (lásd x-note-list).
 */
class NoteController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'noteable_type' => ['required', 'string'],
            'noteable_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
        ]);

        $modelClass = match ($data['noteable_type']) {
            'project' => \App\Models\Project::class,
            'contact' => \App\Models\Contact::class,
            'deal' => \App\Models\Deal::class,
            'retainer' => \App\Models\Retainer::class,
            default => abort(422, 'Ismeretlen noteable_type'),
        };

        $noteable = $modelClass::findOrFail($data['noteable_id']);

        Note::create([
            'noteable_type' => $modelClass,
            'noteable_id' => $noteable->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'note-created');
    }
}

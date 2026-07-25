<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * "Saját jegyzetek" — szabad, semmilyen rekordhoz nem kötött jegyzettömb
 * (Rob kérése, crm_projekt.md 8. szekció 9. pont), a Notion/Evernote
 * "quick notes" mintájára. Minden jegyzet csak a szerzőjéhez tartozik,
 * más felhasználó (még ugyanabban a fiókban is) nem látja/szerkesztheti.
 */
class PersonalNoteController extends Controller
{
    public function index(Request $request): View
    {
        $notes = Note::personal()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('personal-notes.index', ['notes' => $notes]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        Note::create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'note-created');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $this->authorizeOwnPersonalNote($request, $note);

        $data = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $note->update(['body' => $data['body']]);

        return back()->with('status', 'note-updated');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        $this->authorizeOwnPersonalNote($request, $note);

        $note->delete();

        return back()->with('status', 'note-deleted');
    }

    private function authorizeOwnPersonalNote(Request $request, Note $note): void
    {
        if ($note->noteable_type !== null || $note->user_id !== $request->user()->id) {
            throw new AccessDeniedHttpException('Ez nem a te saját jegyzeted.');
        }
    }
}

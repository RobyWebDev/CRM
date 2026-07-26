<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Note;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $tag = $request->string('tag')->toString();

        $contacts = Contact::query()
            ->with('organization', 'tags')
            ->when($request->string('q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($tag !== '', fn ($query) => $query->whereHas('tags', fn ($q) => $q->where('name', $tag)))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('contacts.index', ['contacts' => $contacts, 'q' => $request->string('q'), 'tag' => $tag]);
    }

    public function create(): View
    {
        return view('contacts.create', [
            'organizations' => Organization::orderBy('name')->get(),
            'contacts' => Contact::orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'birthday' => ['nullable', 'date'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'referred_by_contact_id' => ['nullable', 'exists:contacts,id'],
            'note' => ['nullable', 'string', 'max:4096'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        $note = $data['note'] ?? null;
        $tags = $data['tags'] ?? null;
        unset($data['note'], $data['tags']);

        $contact = Contact::create($data);

        // Rob kérése (2026-07-25): rögtön felvételkor is legyen mód szabad szöveges
        // egyedi megjegyzést rögzíteni, ne csak utólag egy külön jegyzet-oldalon.
        if ($note) {
            Note::create([
                'noteable_type' => Contact::class,
                'noteable_id' => $contact->id,
                'user_id' => $request->user()->id,
                'body' => $note,
            ]);
        }

        if ($tags) {
            $contact->syncTagsFromString($tags);
        }

        return redirect()->route('contacts.show', $contact)->with('status', 'contact-created');
    }

    public function show(Contact $contact): View
    {
        $contact->load('organization', 'notes.user', 'tasks', 'tags', 'referredBy', 'referrals');

        return view('contacts.show', ['contact' => $contact]);
    }

    public function edit(Contact $contact): View
    {
        $contact->load('tags');

        return view('contacts.edit', [
            'contact' => $contact,
            'organizations' => Organization::orderBy('name')->get(),
            'contacts' => Contact::where('id', '!=', $contact->id)->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'birthday' => ['nullable', 'date'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'referred_by_contact_id' => ['nullable', 'exists:contacts,id', Rule::notIn([$contact->id])],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        $contact->update($data);
        $contact->syncTagsFromString($tags ?? '');

        return redirect()->route('contacts.show', $contact)->with('status', 'contact-updated');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('status', 'contact-deleted');
    }
}

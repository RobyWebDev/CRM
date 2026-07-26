<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Note;
use App\Models\Organization;
use App\Support\DuplicateFinder;
use App\Support\SelectOrCreate;
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
                        ->orWhere('email', 'like', $term)
                        // A szabadon hozzáadható elérhetőségek/egyedi mezők (pl. második
                        // telefonszám, adószám) is kereshetők legyenek (Rob kérése, 2026-07-26).
                        ->orWhereHas('contactFields', fn ($fq) => $fq->where('value', 'like', $term));
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
        $this->dropEmptyContactFields($request);
        $this->resolveOrganizationId($request);
        $this->resolveReferrerId($request);

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
            'gdpr_consent_given' => ['nullable', 'boolean'],
            'gdpr_consent_note' => ['nullable', 'string', 'max:1000'],
            'contact_fields' => ['nullable', 'array'],
            'contact_fields.*.type' => ['required', 'in:email,phone,address,custom'],
            'contact_fields.*.label' => ['nullable', 'string', 'max:255'],
            'contact_fields.*.value' => ['required', 'string', 'max:1000'],
        ]);

        $note = $data['note'] ?? null;
        $tags = $data['tags'] ?? null;
        $contactFields = $data['contact_fields'] ?? [];
        // GDPR: a hozzájárulás pontos időpontja csak akkor kerül rögzítésre, ha a
        // felhasználó ténylegesen bejelölte — lásd docs/gdpr-terv.md 1. pont
        // (crm_projekt.md 3. szekció "kritikus szabály", eddig nem volt hozzá UI).
        $data['gdpr_consent_at'] = ! empty($data['gdpr_consent_given']) ? now() : null;
        unset($data['note'], $data['tags'], $data['contact_fields'], $data['gdpr_consent_given']);

        $contact = Contact::create($data);
        $this->syncContactFields($contact, $contactFields);

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

        // Nem blokkoló duplikátum-jelzés (CRM best practice, crm_projekt.md 8. szekció) —
        // e-mail vagy telefonszám alapján hasonló meglévő kontaktokra figyelmeztetünk,
        // de a mentést nem akadályozzuk meg.
        $duplicates = DuplicateFinder::find(Contact::class, $contact->email, $contact->phone, $contact->id);

        return redirect()->route('contacts.show', $contact)
            ->with('status', 'contact-created')
            ->with('duplicate_contacts', $duplicates->map(fn ($c) => ['id' => $c->id, 'name' => $c->full_name]));
    }

    public function show(Contact $contact): View
    {
        $contact->load('organization', 'notes.user', 'tasks', 'tags', 'referredBy', 'referrals', 'contactFields');

        return view('contacts.show', ['contact' => $contact]);
    }

    public function edit(Contact $contact): View
    {
        $contact->load('tags', 'contactFields');

        return view('contacts.edit', [
            'contact' => $contact,
            'organizations' => Organization::orderBy('name')->get(),
            'contacts' => Contact::where('id', '!=', $contact->id)->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $this->dropEmptyContactFields($request);
        $this->resolveOrganizationId($request);
        $this->resolveReferrerId($request);

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
            'gdpr_consent_given' => ['nullable', 'boolean'],
            'gdpr_consent_note' => ['nullable', 'string', 'max:1000'],
            'contact_fields' => ['nullable', 'array'],
            'contact_fields.*.type' => ['required', 'in:email,phone,address,custom'],
            'contact_fields.*.label' => ['nullable', 'string', 'max:255'],
            'contact_fields.*.value' => ['required', 'string', 'max:1000'],
        ]);

        $tags = $data['tags'] ?? null;
        $contactFields = $data['contact_fields'] ?? [];
        // GDPR: az eredeti hozzájárulás időpontja megmarad, ha már korábban meg lett
        // adva (nem íródik újra minden mentésnél) — kikapcsoláskor viszont a
        // visszavonást jelezve nullázódik (docs/gdpr-terv.md 1. pont).
        $data['gdpr_consent_at'] = ! empty($data['gdpr_consent_given']) ? ($contact->gdpr_consent_at ?? now()) : null;
        unset($data['tags'], $data['contact_fields'], $data['gdpr_consent_given']);

        $contact->update($data);
        $contact->syncTagsFromString($tags ?? '');
        $this->syncContactFields($contact, $contactFields);

        // Önállóan felismert hiányosság (2026-07-26): a duplikátum-jelzés eddig csak
        // felvételkor futott — ha valaki szerkesztéskor írta át az e-mailt/telefont
        // egy már meglévő kontaktéra, semmilyen figyelmeztetés nem jelent meg.
        $duplicates = DuplicateFinder::find(Contact::class, $contact->email, $contact->phone, $contact->id);

        return redirect()->route('contacts.show', $contact)
            ->with('status', 'contact-updated')
            ->with('duplicate_contacts', $duplicates->map(fn ($c) => ['id' => $c->id, 'name' => $c->full_name]));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('status', 'contact-deleted');
    }

    /**
     * A validáció előtt kiszűri az üresen hagyott "+" sorokat (pl. amikor a
     * felhasználó hozzáad egy elérhetőség-sort, de nem tölti ki) — enélkül a
     * kötelező "value" mező hibát dobna egy szándékosan üresen hagyott sorra.
     */
    private function dropEmptyContactFields(Request $request): void
    {
        $filtered = collect($request->input('contact_fields', []))
            ->filter(fn ($field) => trim((string) ($field['value'] ?? '')) !== '')
            ->values()
            ->all();

        $request->merge(['contact_fields' => $filtered]);
    }

    /**
     * "+ Új szervezet..." feloldása — lásd App\Support\SelectOrCreate (Rob kérése, 2026-07-26).
     */
    private function resolveOrganizationId(Request $request): void
    {
        $request->merge([
            'organization_id' => SelectOrCreate::resolveId(Organization::class, $request->input('organization_id'), $request->input('new_organization_name')),
        ]);
    }

    /**
     * "+ Új kontakt felvétele ajánlóként..." feloldása — a Kampánytól/Szervezettől
     * eltérően itt nem elég egy név, egy VALÓDI kontakt-rekordot hozunk létre (első
     * lépésben csak a megadott alapadatokkal, a többi később a kontakt saját
     * szerkesztő űrlapján bővíthető) — lásd Rob kérése, 2026-07-26.
     */
    private function resolveReferrerId(Request $request): void
    {
        if ($request->input('referred_by_contact_id') !== SelectOrCreate::NEW_OPTION_VALUE) {
            return;
        }

        $firstName = trim((string) $request->input('referrer_first_name'));

        if ($firstName === '') {
            $request->merge(['referred_by_contact_id' => null]);

            return;
        }

        $referrer = Contact::create([
            'first_name' => $firstName,
            'last_name' => trim((string) $request->input('referrer_last_name')) ?: null,
            'email' => trim((string) $request->input('referrer_email')) ?: null,
            'phone' => trim((string) $request->input('referrer_phone')) ?: null,
        ]);

        $request->merge(['referred_by_contact_id' => $referrer->id]);
    }

    /**
     * Teljes csere-szinkron: a beküldött lista alapján újraépíti a kontakt szabadon
     * elnevezhető elérhetőségeit/mezőit (Google Címtár-minta, Rob kérése 2026-07-26).
     */
    private function syncContactFields(Contact $contact, array $fields): void
    {
        $contact->contactFields()->delete();

        foreach (array_values($fields) as $index => $field) {
            $value = trim((string) ($field['value'] ?? ''));

            if ($value === '') {
                continue;
            }

            $label = trim((string) ($field['label'] ?? ''));

            $contact->contactFields()->create([
                'type' => $field['type'] ?? 'custom',
                'label' => $label !== '' ? $label : null,
                'value' => $value,
                'sort_order' => $index,
            ]);
        }
    }
}

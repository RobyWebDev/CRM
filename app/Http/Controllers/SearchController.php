<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Retainer;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Globális gyorskeresés (Rob saját AI-javaslata, crm_projekt.md 8. szekció) —
 * egyetlen keresőmezőbe beírva egyszerre keres a legfontosabb rekordtípusok között.
 * Minden lekérdezés automatikusan account-szűrt a BelongsToAccount global scope miatt.
 */
class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->string('q'));

        if ($q === '') {
            return view('search.index', ['q' => $q, 'results' => []]);
        }

        $like = '%'.$q.'%';

        $contacts = Contact::query()
            ->where(function ($query) use ($like) {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    // A szabadon hozzáadható elérhetőségek/egyedi mezők is kereshetők
                    // legyenek (Rob kérése, 2026-07-26) — pl. egy második telefonszám.
                    ->orWhereHas('contactFields', fn ($fq) => $fq->where('value', 'like', $like));
            })
            ->limit(10)->get();

        $organizations = Organization::query()->where('name', 'like', $like)->limit(10)->get();

        $leads = Lead::query()
            ->where(function ($query) use ($like) {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('company', 'like', $like);
            })
            ->limit(10)->get();

        $deals = Deal::query()->where('title', 'like', $like)->limit(10)->get();
        $projects = Project::query()->where('title', 'like', $like)->limit(10)->get();
        $retainers = Retainer::query()->where('title', 'like', $like)->limit(10)->get();
        $campaigns = Campaign::query()->where('name', 'like', $like)->limit(10)->get();

        return view('search.index', [
            'q' => $q,
            'results' => [
                'contacts' => $contacts,
                'organizations' => $organizations,
                'leads' => $leads,
                'deals' => $deals,
                'projects' => $projects,
                'retainers' => $retainers,
                'campaigns' => $campaigns,
            ],
        ]);
    }
}

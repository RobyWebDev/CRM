<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

/**
 * Fiók-szintű, összesített audit napló (crm_projekt.md 8. szekció, "Admin-szabadság"
 * lista 5. pontja) — a rekordonkénti aktivitás-idővonal (<x-activity-timeline>) már
 * megvolt, csak egy kattintható, szűrhető, ÖSSZES rekordot átfogó lista hiányzott.
 *
 * Az `activity_log` tábla (spatie/laravel-activitylog) NEM rendelkezik saját
 * account_id oszloppal, ezért a tenant-elkülönítést itt kézzel kell biztosítani:
 * csak az aktuális fiók FELHASZNÁLÓI által okozott (causer_id) bejegyzéseket
 * mutatjuk — mivel minden művelet a BelongsToAccount global scope-on keresztül
 * fut, a causer mindig ugyanahhoz a fiókhoz tartozik, mint az érintett rekord.
 */
class ActivityLogController extends Controller
{
    private const SUBJECT_ROUTES = [
        Contact::class => 'contacts.show',
        Deal::class => 'deals.edit',
        Lead::class => 'leads.edit',
        Project::class => 'projects.show',
        Retainer::class => 'retainers.show',
    ];

    private const SUBJECT_LABELS = [
        Contact::class => 'Kontakt',
        Deal::class => 'Üzlet',
        Lead::class => 'Lead',
        Project::class => 'Projekt',
        Retainer::class => 'Retainer',
    ];

    public function index(Request $request): View
    {
        $accountUserIds = User::where('account_id', $request->user()->account_id)->pluck('id');
        $subjectType = $request->string('subject_type')->toString();

        $activities = Activity::query()
            ->whereIn('causer_id', $accountUserIds)
            ->where('causer_type', User::class)
            ->when($subjectType !== '' && array_key_exists($subjectType, self::SUBJECT_LABELS), fn ($query) => $query->where('subject_type', $subjectType))
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('activity-log.index', [
            'activities' => $activities,
            'subjectType' => $subjectType,
            'subjectLabels' => self::SUBJECT_LABELS,
            'subjectRoutes' => self::SUBJECT_ROUTES,
        ]);
    }
}

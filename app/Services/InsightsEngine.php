<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;

/**
 * Szabályalapú (NEM AI-alapú) figyelemfelhívó javaslatok a Dashboardra —
 * Rob kérése (2026-07-25): "most kevés leaded van, fókuszálj lead-generálásra"
 * jellegű ötletek, egyszerű küszöbértékekkel. Lásd crm_projekt.md 8. szekció
 * "Szabályalapú insights" backlog-tétele — ez ennek első, működő verziója.
 *
 * Szándékosan NEM igényel AI-t: egyszerű számlálásokkal/dátum-összehasonlításokkal
 * dolgozik. Egy jövőbeli, fizetős/AI-szintű előfizetésnél ugyanez a felület
 * valódi AI-alapú, személyre szabottabb javaslatokra cserélhető/bővíthető.
 */
class InsightsEngine
{
    /**
     * @return array<int, array{type: string, message: string}>
     */
    public static function generate(): array
    {
        $insights = [];

        $recentLeads = Lead::where('created_at', '>=', now()->subDays(14))->count();
        if ($recentLeads < 2) {
            $insights[] = [
                'type' => 'warning',
                'message' => __('Az elmúlt 14 napban kevés (:count) új leaded volt — érdemes lehet a lead-generálásra fókuszálni (hirdetések, ajánláskérés, hideghívás).', ['count' => $recentLeads]),
            ];
        }

        $stalledDeals = Deal::where('status', 'open')
            ->get()
            ->filter(fn (Deal $deal) => $deal->daysInStage() >= 14)
            ->count();
        if ($stalledDeals > 0) {
            $insights[] = [
                'type' => 'warning',
                'message' => __(':count üzleted legalább 14 napja nem lépett tovább a pipeline-on — érdemes utánuk nézni, mielőtt lehűlnek.', ['count' => $stalledDeals]),
            ];
        }

        $overdueTasks = Task::where('status', 'open')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();
        if ($overdueTasks > 0) {
            $insights[] = [
                'type' => 'danger',
                'message' => __(':count lejárt határidejű, még nyitott teendőd van.', ['count' => $overdueTasks]),
            ];
        }

        $leadsWithoutNextStep = Lead::whereNotIn('status', ['converted', 'unqualified'])
            ->where(function ($query) {
                $query->whereNull('next_step')->orWhere('next_step', '');
            })
            ->count();
        if ($leadsWithoutNextStep > 0) {
            $insights[] = [
                'type' => 'info',
                'message' => __(':count nyitott leadednél nincs megadva következő lépés — érdemes rögzíteni, mi legyen a soron következő teendő.', ['count' => $leadsWithoutNextStep]),
            ];
        }

        // A "következő lépés" dátuma (next_step_due_at) szabad szöveges mező, NEM a
        // tényleges Teendő-rendszer része — enélkül a szabály nélkül egy lejárt
        // határidejű lead-következő-lépés sehol nem jelenne meg (2026-07-26,
        // önállóan felismert hiányosság, lásd docs/haladasi-naplo.md).
        $leadsWithOverdueNextStep = Lead::whereNotIn('status', ['converted', 'unqualified'])
            ->whereNotNull('next_step_due_at')
            ->where('next_step_due_at', '<', now())
            ->count();
        if ($leadsWithOverdueNextStep > 0) {
            $insights[] = [
                'type' => 'danger',
                'message' => __(':count leadednél lejárt a következő lépés határideje — érdemes utánanézni.', ['count' => $leadsWithOverdueNextStep]),
            ];
        }

        $contactsWithoutPhone = Contact::whereNull('phone')->orWhere('phone', '')->count();
        if ($contactsWithoutPhone > 0) {
            $insights[] = [
                'type' => 'info',
                'message' => __(':count kontaktnak nincs megadva telefonszáma — érdemes lehet pótolni, hogy könnyebben elérhetőek legyenek.', ['count' => $contactsWithoutPhone]),
            ];
        }

        if (Lead::count() === 0 && Deal::count() === 0) {
            $insights[] = [
                'type' => 'info',
                'message' => __('Még nincs egyetlen leaded vagy üzleted sem — kezdd az első lead vagy kontakt felvételével.'),
            ];
        }

        return $insights;
    }
}

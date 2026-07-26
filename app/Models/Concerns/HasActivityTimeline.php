<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Aktivitás-idővonal — ki mikor mit módosított egy rekordon (Rob kérése,
 * crm_projekt.md 8. szekció "best practice ötletek"), a spatie/laravel-
 * activitylog csomagra építve (`activity_log` tábla, eddig telepítve volt,
 * de egyetlen modellen sem volt bekötve — 2026-07-26-tól tényleg naplóz).
 * Csak a ténylegesen megváltozott, kitölthető mezőket naplózzuk.
 */
trait HasActivityTimeline
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

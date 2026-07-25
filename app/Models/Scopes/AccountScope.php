<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Tenant-elkülönítés: minden lekérdezést a bejelentkezett user account_id-jára szűkít.
 * A super admin (users.is_super_admin) kivétel — ő minden accountot lát (lásd jogosultsagok-terv.md).
 */
class AccountScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check() || auth()->user()->is_super_admin) {
            return;
        }

        $builder->where($model->getTable().'.account_id', auth()->user()->account_id);
    }
}

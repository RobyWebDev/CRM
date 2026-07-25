<?php

namespace App\Models\Concerns;

use App\Models\Account;
use App\Models\Scopes\AccountScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ezt használja minden tenant-scope-olt modell (Contact, Deal, Project, stb.).
 * Ez valósítja meg a legkritikusabb biztonsági szabályt: account-elkülönítés,
 * lásd mappastruktura-terv.md 3. pont és teszterv.md.
 */
trait BelongsToAccount
{
    protected static function bootBelongsToAccount(): void
    {
        static::addGlobalScope(new AccountScope);

        static::creating(function ($model) {
            if (! $model->account_id && auth()->check()) {
                $model->account_id = auth()->user()->account_id;
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Models\Concerns\HasActivityTimeline;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ismétlődő (havi/negyedéves) megbízás — külön a Project (egyszeri) modelltől.
 * Döntés: 2026-07-25, lásd crm_projekt.md 7. szekció.
 */
#[Fillable([
    'account_id', 'deal_id', 'contact_id', 'organization_id', 'service_type_id',
    'owner_user_id', 'title', 'description', 'monthly_fee', 'billing_cycle',
    'billing_day', 'status', 'started_at', 'ended_at', 'custom_fields',
])]
class Retainer extends Model
{
    use BelongsToAccount, HasActivityTimeline, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'monthly_fee' => 'decimal:2',
            'started_at' => 'date',
            'ended_at' => 'date',
            'custom_fields' => 'array',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(RetainerInvoice::class);
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}

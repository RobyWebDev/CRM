<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Models\Concerns\HasActivityTimeline;
use App\Models\Concerns\HasPersonName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Még nem minősített érdeklődő — a Contacttól külön, amíg nem derül ki, hogy valódi
 * kapcsolat-e (CRM best practice, pl. Salesforce Lead objektuma). "Konvertáláskor"
 * (ld. LeadController::convert) lesz belőle Contact, opcionálisan Deal is.
 */
#[Fillable([
    'account_id', 'owner_user_id', 'service_type_id', 'campaign_id', 'first_name', 'last_name',
    'email', 'phone', 'company', 'project_title', 'source', 'status', 'current_status_note',
    'next_step', 'next_step_due_at', 'win_probability', 'comment', 'custom_fields',
    'converted_at', 'converted_contact_id', 'converted_deal_id',
])]
class Lead extends Model
{
    use BelongsToAccount, HasActivityTimeline, HasFactory, HasPersonName, SoftDeletes;

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'converted_at' => 'datetime',
            'next_step_due_at' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedDeal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'converted_deal_id');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }
}

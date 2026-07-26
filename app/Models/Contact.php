<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Models\Concerns\HasActivityTimeline;
use App\Models\Concerns\HasPersonName;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'account_id', 'organization_id', 'owner_user_id', 'referred_by_contact_id', 'first_name',
    'last_name', 'job_title', 'email', 'phone', 'birthday', 'website', 'address', 'source',
    'gdpr_consent_at', 'gdpr_consent_note', 'custom_fields',
])]
class Contact extends Model
{
    use BelongsToAccount, HasActivityTimeline, HasFactory, HasPersonName, HasTags, SoftDeletes;

    protected function casts(): array
    {
        return [
            'gdpr_consent_at' => 'datetime',
            'birthday' => 'date',
            'custom_fields' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** Melyik meglévő kontakt ajánlotta — Salesforce referral-partner minta, lásd ugyfelszerzes-terv.md 3.1. pont. */
    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'referred_by_contact_id');
    }

    /** Ez a kontakt kiket ajánlott (a referredBy fordítottja). */
    public function referrals(): HasMany
    {
        return $this->hasMany(Contact::class, 'referred_by_contact_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function retainers(): HasMany
    {
        return $this->hasMany(Retainer::class);
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

    /** Tetszőleges számú, elnevezhető elérhetőség/mező — Google Címtár-minta, lásd contact_fields migráció. */
    public function contactFields(): HasMany
    {
        return $this->hasMany(ContactField::class)->orderBy('sort_order');
    }

    /**
     * A contactFields listája megjelenítésre kész címkével — az e-mail/telefon/cím
     * típusú, még el nem nevezett mezők egy alapértelmezett típusnevet kapnak, a
     * "custom" típusú, el nem nevezett mezők pedig sorban "Egyedi mező 1", "Egyedi
     * mező 2" stb. néven jelennek meg, amíg a felhasználó át nem nevezi őket
     * (Rob kérése, 2026-07-26).
     */
    public function contactFieldsWithDisplayLabels(): \Illuminate\Support\Collection
    {
        $customCounter = 0;

        return $this->contactFields->map(function (ContactField $field) use (&$customCounter) {
            $label = trim((string) $field->label);

            if ($label === '') {
                if ($field->type === 'custom') {
                    $customCounter++;
                    $label = __('Egyedi mező :n', ['n' => $customCounter]);
                } else {
                    $label = match ($field->type) {
                        'email' => __('E-mail'),
                        'phone' => __('Telefon'),
                        'address' => __('Cím'),
                        default => __('Mező'),
                    };
                }
            }

            return (object) ['type' => $field->type, 'label' => $label, 'value' => $field->value];
        });
    }
}

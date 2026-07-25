<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['account_id', 'name', 'website', 'industry', 'custom_fields'])]
class Organization extends Model
{
    use BelongsToAccount, HasFactory, HasTags, SoftDeletes;

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
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
}

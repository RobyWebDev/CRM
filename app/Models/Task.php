<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['account_id', 'taskable_type', 'taskable_id', 'assigned_user_id', 'title', 'description', 'due_date', 'status', 'completed_at', 'recurrence'])]
class Task extends Model
{
    use BelongsToAccount, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Ismétlődő teendő (MiniCRM-inspiráció, docs/minicrm-inspiracio.md 5. pont): a következő
     * előfordulás határideje, a jelenlegi due_date-hez (vagy mának) képest.
     */
    public function nextDueDate(): ?\Illuminate\Support\Carbon
    {
        $base = $this->due_date ?? now();

        return match ($this->recurrence) {
            'daily' => $base->copy()->addDay(),
            'weekly' => $base->copy()->addWeek(),
            'monthly' => $base->copy()->addMonthNoOverflow(),
            default => null,
        };
    }
}

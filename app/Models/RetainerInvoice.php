<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Egy Retainer havi/negyedéves számlázási periódusa. MVP: csak követés-státusz.
 */
#[Fillable(['account_id', 'retainer_id', 'period_start', 'period_end', 'amount', 'invoice_status', 'issued_at', 'paid_at'])]
class RetainerInvoice extends Model
{
    use BelongsToAccount, HasFactory;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function retainer(): BelongsTo
    {
        return $this->belongsTo(Retainer::class);
    }
}

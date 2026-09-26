<?php

namespace App\Models;

use Database\Factories\EventExpensePaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventExpensePayment extends Model
{
    /** @use HasFactory<EventExpensePaymentFactory> */
    use HasFactory;

    protected $fillable = ['amount_minor', 'paid_on', 'note'];

    protected function casts(): array
    {
        return ['amount_minor' => 'integer', 'paid_on' => 'date:Y-m-d'];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(EventExpense::class, 'event_expense_id');
    }
}

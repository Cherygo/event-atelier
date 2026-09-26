<?php

namespace App\Models;

use Database\Factories\EventExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventExpense extends Model
{
    /** @use HasFactory<EventExpenseFactory> */
    use HasFactory;

    protected $fillable = ['title', 'category', 'estimated_minor', 'actual_minor', 'due_date', 'notes'];

    protected function casts(): array
    {
        return ['estimated_minor' => 'integer', 'actual_minor' => 'integer', 'due_date' => 'date:Y-m-d', 'paid_minor' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EventExpensePayment::class);
    }

    /** Serialize an expense loaded with its payments and paid_minor aggregate. @return array<string, mixed> */
    public function budgetData(): array
    {
        $paid = (int) $this->paid_minor;
        $outstanding = $this->actual_minor === null ? null : $this->actual_minor - $paid;

        return array_merge($this->toArray(), [
            'paid_minor' => $paid,
            'outstanding_minor' => $outstanding,
            'payment_status' => match (true) {
                $this->actual_minor === null => 'Unconfirmed',
                $this->actual_minor === 0 => 'No cost',
                $outstanding === 0 => 'Paid',
                $paid > 0 => 'Part-paid',
                default => 'Unpaid',
            },
            'overdue' => $outstanding > 0 && $this->due_date?->isBefore(today()),
        ]);
    }
}

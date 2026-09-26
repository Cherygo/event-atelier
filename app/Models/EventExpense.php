<?php

namespace App\Models;

use Database\Factories\EventExpenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventExpense extends Model
{
    /** @use HasFactory<EventExpenseFactory> */
    use HasFactory;

    protected $fillable = ['title', 'category', 'estimated_minor', 'notes'];

    protected function casts(): array
    {
        return ['estimated_minor' => 'integer'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

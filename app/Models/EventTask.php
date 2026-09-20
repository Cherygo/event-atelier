<?php

namespace App\Models;

use App\TaskStatus;
use Database\Factories\EventTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTask extends Model
{
    /** @use HasFactory<EventTaskFactory> */
    use HasFactory;

    protected $fillable = ['title', 'notes', 'category', 'due_date'];

    protected function casts(): array
    {
        return ['status' => TaskStatus::class, 'completed_at' => 'datetime', 'due_date' => 'date:Y-m-d'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

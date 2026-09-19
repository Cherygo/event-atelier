<?php

namespace App\Models;

use App\EventRole;
use Database\Factories\EventMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventMember extends Model
{
    /** @use HasFactory<EventMemberFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'role'];

    protected function casts(): array
    {
        return ['role' => EventRole::class];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

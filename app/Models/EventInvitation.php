<?php

namespace App\Models;

use App\EventRole;
use Database\Factories\EventInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventInvitation extends Model
{
    /** @use HasFactory<EventInvitationFactory> */
    use HasFactory;

    protected $fillable = ['email', 'role', 'invited_by', 'token_hash', 'expires_at'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['role' => EventRole::class, 'expires_at' => 'datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isUsable(): bool
    {
        return $this->expires_at->isFuture()
            && $this->inviter !== null
            && $this->inviter->can('invite', [$this->event, $this->role->value]);
    }
}

<?php

namespace App\Models;

use App\EventRole;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'event_date',
        'guest_count',
        'location',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'guest_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(EventMember::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EventTask::class);
    }

    public function assignableUsers(): Builder
    {
        return User::query()->where(function (Builder $query): void {
            $query->where('id', $this->user_id)->orWhereIn('id', $this->members()->select('user_id')->whereIn('role', [EventRole::Admin, EventRole::Editor]));
        });
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EventInvitation::class);
    }

    public function roleFor(User $user): ?EventRole
    {
        if ($this->user_id === $user->id) {
            return EventRole::Owner;
        }

        if ($this->relationLoaded('members')) {
            return $this->members->firstWhere('user_id', $user->id)?->role;
        }

        return $this->members()->where('user_id', $user->id)->first()?->role;
    }
}

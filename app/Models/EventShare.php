<?php

namespace App\Models;

use Database\Factories\EventShareFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EventShare extends Model
{
    /** @use HasFactory<EventShareFactory> */
    use HasFactory;

    protected $fillable = ['show_date', 'show_location', 'public_note'];

    protected $hidden = ['token', 'token_hash'];

    protected function casts(): array
    {
        return ['token' => 'encrypted', 'show_date' => 'boolean', 'show_location' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function publish(): void
    {
        $token = Str::random(64);
        $this->forceFill(['token' => $token, 'token_hash' => hash('sha256', $token)])->save();
    }

    public function revoke(): void
    {
        $this->forceFill(['token' => null, 'token_hash' => null])->save();
    }
}

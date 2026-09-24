<?php

namespace App\Models;

use Database\Factories\EventVendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventVendor extends Model
{
    /** @use HasFactory<EventVendorFactory> */
    use HasFactory;

    protected $fillable = ['name', 'category', 'contact_name', 'email', 'phone', 'website', 'notes'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

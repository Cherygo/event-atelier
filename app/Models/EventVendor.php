<?php

namespace App\Models;

use App\VendorStatus;
use Database\Factories\EventVendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventVendor extends Model
{
    /** @use HasFactory<EventVendorFactory> */
    use HasFactory;

    public const CURRENCIES = ['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'CHF', 'NZD'];

    protected $fillable = ['name', 'category', 'contact_name', 'email', 'phone', 'website', 'notes', 'status', 'quote_amount', 'currency', 'quote_details'];

    protected function casts(): array
    {
        return ['status' => VendorStatus::class, 'quote_amount' => 'decimal:2'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

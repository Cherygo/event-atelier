<?php

namespace App;

use App\Models\Event;
use App\Models\EventShare;

class SharedEventData
{
    /** @return array{name: string, date: ?string, location: ?string, note: ?string} */
    public function forEvent(Event $event, EventShare $share): array
    {
        return [
            'name' => $event->name,
            'date' => $share->show_date ? $event->event_date?->toDateString() : null,
            'location' => $share->show_location ? $event->location : null,
            'note' => $share->public_note,
        ];
    }
}

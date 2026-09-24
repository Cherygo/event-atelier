<?php

namespace App;

enum VendorStatus: string
{
    case Researching = 'researching';
    case Contacted = 'contacted';
    case Shortlisted = 'shortlisted';
    case Booked = 'booked';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Researching => 'Researching',
            self::Contacted => 'Contacted',
            self::Shortlisted => 'Shortlisted',
            self::Booked => 'Booked',
            self::Declined => 'Declined',
        };
    }
}

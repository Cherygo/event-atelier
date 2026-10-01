<?php

namespace App;

use Carbon\CarbonInterface;

class TaskTemplateCatalog
{
    private const ITEMS = [
        'outline-budget' => ['title' => 'Outline the event budget', 'category' => 'Budget', 'notes' => 'Agree a working target and leave room for unexpected costs.', 'days_before' => 90],
        'guest-list' => ['title' => 'Draft the guest list', 'category' => 'Guests', 'notes' => 'Estimate numbers before confirming venue and catering arrangements.', 'days_before' => 75],
        'venue' => ['title' => 'Confirm the venue', 'category' => 'Venue', 'notes' => 'Check capacity, accessibility, opening times, and what is included.', 'days_before' => 60],
        'catering' => ['title' => 'Plan food and refreshments', 'category' => 'Catering', 'notes' => 'Discuss the menu and a way to collect dietary requirements.', 'days_before' => 30],
        'guest-details' => ['title' => 'Share arrival details with guests', 'category' => 'Guests', 'notes' => 'Confirm timings, transport, and any practical details guests need.', 'days_before' => 7],
        'supplier-check' => ['title' => 'Confirm arrangements with booked vendors', 'category' => 'Vendors', 'notes' => 'Review arrival times, contacts, and responsibilities.', 'days_before' => 7],
        'wedding-ceremony' => ['title' => 'Plan the ceremony', 'category' => 'Ceremony', 'notes' => 'Agree the format, participants, and any requirements with your ceremony provider.', 'days_before' => 60],
        'wedding-photography' => ['title' => 'Discuss photography plans', 'category' => 'Photography', 'notes' => 'Decide which moments and group photographs matter to you.', 'days_before' => 45],
        'wedding-seating' => ['title' => 'Prepare the seating plan', 'category' => 'Guests', 'notes' => 'Check the latest guest list and venue layout before assigning places.', 'days_before' => 14],
        'corporate-objectives' => ['title' => 'Agree the event objectives', 'category' => 'Programme', 'notes' => 'Define the audience, intended outcomes, and who approves key decisions.', 'days_before' => 90],
        'corporate-programme' => ['title' => 'Build the programme', 'category' => 'Programme', 'notes' => 'Set session timings, breaks, and the people responsible for each part.', 'days_before' => 45],
        'corporate-av' => ['title' => 'Check presentation and AV requirements', 'category' => 'Production', 'notes' => 'Confirm microphones, screens, connectivity, and an on-site test.', 'days_before' => 14],
        'private-format' => ['title' => 'Choose the shape of the gathering', 'category' => 'Planning', 'notes' => 'Decide on the atmosphere, activities, and a simple running order.', 'days_before' => 45],
        'private-space' => ['title' => 'Plan seating and the gathering space', 'category' => 'Venue', 'notes' => 'Allow space for arrivals, food, seating, and any activities.', 'days_before' => 14],
    ];

    /** @return list<array{key: string, name: string, description: string, event_types: list<string>, items: list<array{key: string, title: string, category: string, notes: string, days_before: int}>}> */
    public function all(): array
    {
        $templates = [
            ['key' => 'wedding', 'name' => 'Wedding', 'description' => 'A starting point for your ceremony and celebration.', 'event_types' => ['wedding'], 'items' => ['outline-budget', 'guest-list', 'venue', 'wedding-ceremony', 'wedding-photography', 'catering', 'wedding-seating', 'supplier-check', 'guest-details']],
            ['key' => 'corporate', 'name' => 'Corporate event', 'description' => 'Bring the audience, programme, and production together.', 'event_types' => ['corporate', 'conference'], 'items' => ['corporate-objectives', 'outline-budget', 'guest-list', 'venue', 'corporate-programme', 'catering', 'corporate-av', 'supplier-check', 'guest-details']],
            ['key' => 'private', 'name' => 'Private gathering', 'description' => 'Practical essentials for a dinner, party, or other occasion.', 'event_types' => ['private', 'custom'], 'items' => ['outline-budget', 'guest-list', 'venue', 'private-format', 'catering', 'private-space', 'supplier-check', 'guest-details']],
        ];

        return array_map(function (array $template): array {
            $template['items'] = array_map(fn (string $key): array => ['key' => $key, ...self::ITEMS[$key]], $template['items']);

            return $template;
        }, $templates);
    }

    /** @return array<string, mixed>|null */
    public function find(string $key): ?array
    {
        foreach ($this->all() as $template) {
            if ($template['key'] === $key) {
                return $template;
            }
        }

        return null;
    }

    public function suggestedDueDate(?CarbonInterface $eventDate, int $daysBefore, CarbonInterface $today): ?string
    {
        $date = $eventDate?->copy()->subDays($daysBefore)->toDateString();

        return $date !== null && $date >= $today->toDateString() ? $date : null;
    }

    /** @return list<array<string, mixed>> */
    public function withSuggestedDates(?CarbonInterface $eventDate, CarbonInterface $today): array
    {
        return array_map(function (array $template) use ($eventDate, $today): array {
            $template['items'] = array_map(fn (array $item): array => [
                ...$item, 'due_date' => $this->suggestedDueDate($eventDate, $item['days_before'], $today),
            ], $template['items']);

            return $template;
        }, $this->all());
    }
}

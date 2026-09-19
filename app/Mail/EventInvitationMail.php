<?php

namespace App\Mail;

use App\Models\EventInvitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use SerializesModels;

    public function __construct(public EventInvitation $invitation, public string $acceptUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You’re invited to plan '.$this->invitation->event->name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.event-invitation');
    }
}

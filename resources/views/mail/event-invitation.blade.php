<x-mail::message>
# You’re invited

{{ $invitation->inviter->name }} has invited you to **{{ $invitation->event->name }}** on Event Atelier as a **{{ $invitation->role->label() }}**.

<x-mail::button :url="$acceptUrl">
Review invitation
</x-mail::button>

Sign in with **{{ $invitation->email }}** to accept. If you don’t have an account, you can create one first. Creating an account is free.

This invitation expires in seven days. If you weren’t expecting it, you can ignore this email.

Event Atelier
</x-mail::message>

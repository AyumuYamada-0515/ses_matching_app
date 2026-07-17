<?php

namespace App\Mail;

use App\Models\AssignmentInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssignmentInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AssignmentInvitation $invitation)
    {
        $this->invitation->loadMissing('salesRepresentative');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '担当営業の勧誘が届きました');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.assignment-invitation');
    }
}

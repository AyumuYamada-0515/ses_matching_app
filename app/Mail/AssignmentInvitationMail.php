<?php

namespace App\Mail;

use App\Models\AssignmentInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssignmentInvitationMail extends Mailable implements ShouldQueueAfterCommit
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300, 900];

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

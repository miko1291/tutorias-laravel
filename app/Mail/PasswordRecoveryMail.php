<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordRecoveryMail extends Mailable
{
    public function __construct(public string $username, public string $resetUrl, public int $expiresIn) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Recupera tu acceso · Tutorías ITSVA');
    }

    public function content(): Content
    {
        return new Content(html: 'mail.password-recovery', text: 'mail.password-recovery-text');
    }
}

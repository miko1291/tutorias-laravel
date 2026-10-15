<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NotificationMail extends Mailable
{
    // 'message' colisiona con el objeto Message que Laravel inyecta en las
    // vistas de correo; la variable de la plantilla se llama 'contenido'.
    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope;
        if ($address = config('tutorias.support_email')) {
            $envelope->from($address, 'Control de Tutorías · ITSVA');
        }

        return $envelope->subject($this->title);
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.notification',
            text: 'mail.notification-text',
            with: [
                'title' => $this->title,
                'contenido' => $this->message,
                'url' => $this->url,
            ],
        );
    }
}

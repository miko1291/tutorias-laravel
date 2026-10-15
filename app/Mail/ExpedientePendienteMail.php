<?php

namespace App\Mail;

use App\Models\GrupoMaster;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ExpedientePendienteMail extends Mailable
{
    public function __construct(
        public GrupoMaster $grupo,
        public array $pendientes,
        public int $total,
        public string $testEmail = '',
        public bool $modoNotificacionPrueba = false,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope;
        if ($address = config('tutorias.support_email')) {
            $envelope->from($address, 'Control de Tutorías · ITSVA');
        }

        return $envelope->subject('Prueba controlada · expedientes pendientes');
    }

    public function content(): Content
    {
        return new Content(
            html: 'mail.expediente-pendiente',
            text: 'mail.expediente-pendiente-text',
            with: [
                'grupo' => $this->grupo,
                'pendientes' => $this->pendientes,
                'total' => $this->total,
                'testEmail' => $this->testEmail,
                'modoNotificacionPrueba' => $this->modoNotificacionPrueba,
                'formularioUrl' => url('/expediente/index'),
            ],
        );
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionFinal extends Mailable
{
    use Queueable, SerializesModels;

    public $cop;
    public $numero_documento_paciente_odontologo;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cop,$numero_documento_paciente_odontologo)
    {
        $this->cop = $cop;
        $this->numero_documento_paciente_odontologo = $numero_documento_paciente_odontologo;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'REGISTRO EXITOSO',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mailFinal',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}

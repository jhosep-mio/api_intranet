<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationDoctor extends Mailable
{
    use Queueable, SerializesModels;

    public $nameDoctor;
    public $name;
    public $user_odontologo;
    public $pass_odontologo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nameDoctor,$name,$user_odontologo,$pass_odontologo)
    {
        $this->nameDoctor = $nameDoctor;
        $this->name = $name;
        $this->user_odontologo = $user_odontologo;
        $this->pass_odontologo = $pass_odontologo;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Entrega de resultados',
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
            view: 'mailDoctor',
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

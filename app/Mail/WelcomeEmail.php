<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $client;
    public $compte;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password, $client = null, $compte = null)
    {
        $this->user = $user;
        $this->password = $password;
        $this->client = $client;
        $this->compte = $compte;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur API Finance - Vos identifiants de connexion',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'user' => $this->user,
                'password' => $this->password,
                'client' => $this->client,
                'compte' => $this->compte,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

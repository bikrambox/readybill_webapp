<?php

namespace Modules\Agent\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $activationUrl;

    public function __construct(string $token, string $activationUrl)
    {
        $this->token = $token;
        $this->activationUrl = $activationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Readybill Agents - Email Verification', // ✅ Inbox heading
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'agent::emails.verify-email',
            with: [
                'activationUrl' => $this->activationUrl
            ],
        );
    }
}
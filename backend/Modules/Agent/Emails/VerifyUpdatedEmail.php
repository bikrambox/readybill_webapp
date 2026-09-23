<?php

namespace Modules\Agent\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;

class VerifyUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public string $token;
    public string $verificationUrl;

    public function __construct(string $token, string $verificationUrl)
    {
        $this->token = $token;
        $this->verificationUrl = $verificationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Readybill Agents - Email Verification', // ✅ Inbox heading
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function content(): Content
    {
        return new Content(
            view: 'agent::emails.verify-new-email',
            with: [
                'activationUrl' => $this->verificationUrl
            ],
        );
    }
}

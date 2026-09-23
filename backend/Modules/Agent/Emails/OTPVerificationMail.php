<?php

namespace Modules\Agent\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $otp;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp)
    {
        $this->otp = $otp;  // Pass the OTP to the email
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Customize the email subject and view
        return $this->from('no-reply@readybill.app', 'Ready Bill')
            ->subject('Your OTP for Password Change')
            ->view('agent::emails.otp')  // Specify the view for the email content
            ->with([
                'otp' => $this->otp,  // Pass the OTP to the email view
            ]);
    }
}

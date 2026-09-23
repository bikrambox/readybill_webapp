<?php

namespace Modules\Admin\Emails;

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

    public function build()
    {
        // Customize the email subject and view
        return $this->from('no-reply@readybill.app', 'Ready Bill')
            ->subject('Your OTP for Password Change')
            ->view('admin::email.otp')  // Specify the view for the email content
            ->with([
                'otp' => $this->otp,  // Pass the OTP to the email view
            ]);
    }
}

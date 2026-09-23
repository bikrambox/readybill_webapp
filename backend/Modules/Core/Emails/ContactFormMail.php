<?php

namespace Modules\Core\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $formData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($formData)
    {
        $this->formData = $formData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('support@readybill.app', 'Ready Bill')
            ->subject('New Contact Form Submission')
            ->view('coreweb::email.contact-form');
    }
}

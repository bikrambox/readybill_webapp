<?php

namespace Modules\Core\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class QuerySubmittedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $queryData;
    public $attachment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($queryData, $attachment)
    {
        $this->queryData = $queryData;
        $this->attachment = $attachment;
    }


    public function envelope()
    {
        return new Envelope(
            subject: 'Ready Bill Questions',
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from('support@readybill.app', 'Ready Bill')
            ->view('coreweb::email.query_submitted') // The view for the email
            ->with([
                'title' => $this->queryData['title'],
                'description' => $this->queryData['description'],
                'entity_id' => $this->queryData['entity_id'],
                'userName' => $this->queryData['userName'],
                'shopBusinessName' => $this->queryData['shopBusinessName'],
                'shopEmail' => $this->queryData['shopEmail'],
                'shopMobileNumber' => $this->queryData['shopMobileNumber'],
            ])
            ->subject('Query Submitted: ' . $this->queryData['title']);

        // Attach the file if there is one
        if ($this->attachment && file_exists(public_path($this->attachment))) {
            $mail->attach(public_path($this->attachment));
        }

        return $mail;
    }
}

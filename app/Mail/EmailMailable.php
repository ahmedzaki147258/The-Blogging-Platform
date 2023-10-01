<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $user, $code, $is_verify;
    /**
     * Create a new message instance.
     */
    public function __construct($user, $code, $is_verify)
    {
        $this->user = $user;
        $this->code = $code;
        $this->is_verify = $is_verify;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Mailable',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        if($this->is_verify){
            return new Content(view: 'verify');
        } else{
            return new Content(view: 'reset');
        }
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            // Attachment::fromPath('path file')
        ];
    }
}

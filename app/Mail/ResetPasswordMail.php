<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Password Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     // return new Content(
    //     //     view: 'view.name',
    //     // );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }


    public $token;
    public $userName;

    public function __construct($token, $userName)
    {
        $this->token = $token;
        $this->userName = $userName;
    }
    public function build()
    {
        $link = url("http://localhost:8002/resetpassword/{$this->token}");

         return $this->subject('Reset Your Password')
            ->view('emails.reset_password')
            ->with([
                'name' => $this->userName,
                'link' => $link
            ]);    
          
    }
}

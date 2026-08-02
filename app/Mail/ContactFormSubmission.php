<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public array $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function build()
    {
        return $this
            ->subject('New contact form message: ' . $this->content['subject'])
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo($this->content['email'], $this->content['name'])
            ->view('emails.contact-message')
            ->with([
                'name' => $this->content['name'],
                'email' => $this->content['email'],
                'subject' => $this->content['subject'],
                'messageBody' => $this->content['message'],
            ]);
    }
}

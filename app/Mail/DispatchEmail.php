<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DispatchEmail extends Mailable
{
    use Queueable, SerializesModels;

    public array $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    public function build()
    {
        $email = $this->subject($this->emailData['subject'])
            ->view('emails.dispatch')
            ->with([
                'messageBody' => $this->emailData['message'],
                'subject' => $this->emailData['subject'],
            ]);

        if (!empty($this->emailData['attachment'])) {
            $email->attach(storage_path('app/' . $this->emailData['attachment']));
        }

        return $email;
    }
}

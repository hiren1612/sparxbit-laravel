<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BitReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public Collection $bits)
    {
    }

    public function build(): self
    {
        $count = $this->bits->count();

        return $this->subject($count === 1
                ? 'Reminder: '.$this->bits->first()->title
                : "You've got {$count} Bits to complete")
            ->view('emails.bit-reminder');
    }
}

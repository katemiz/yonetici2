<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ManagerAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $manager,
        public string $resetToken,
    ) {
    }

    public function build(): self
    {
        return $this->subject('Yönetici Hesabınız Oluşturuldu')
            ->view('emails.manager-account-created');
    }
}

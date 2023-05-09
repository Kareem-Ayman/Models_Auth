<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $user_code;
    public $user_token;

    public function __construct($user_code, $user_token)
    {
        $this->user_code = $user_code;
        $this->user_token = $user_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Here we are')->view('mails.myTestMail', ["user_code"=> $this->user_code, "user_token"=> $this->user_token]);
    }
}

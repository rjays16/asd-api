<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Invoice extends Mailable
{
    use Queueable, SerializesModels;
    
    public $user;
    public $payment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    

    public function __construct($user, $payment)
    {
        $this->user = $user;
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.invoice')
            ->subject("Confirmation of Payment for the 4th Asian Society of Dermatopathology Annual Meeting")
            ->with(array('user' => $this->user, 'payment' => $this->payment));
    }
}
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Loginmail extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $data;

    public function __construct($data)
    {
        $this->data= $data;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->from('service@deliveringparcel.com')->subject('Login Detail From Delivering Parcel')->view('Login_mail')->with('data', $this->data);
        return $this->from('service@deliveringparcel.com')->subject('Login Detail From Delivering Parcel')->view('Login_mail');
    }
}

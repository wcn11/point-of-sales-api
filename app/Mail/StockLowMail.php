<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class StockLowMail extends Mailable //implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $stocks;
    protected $user;

    public function __construct($stocks, $user)
    {
        $this->stocks = $stocks;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
            ->to(env("STOCK_MAIL_RECEIVER"))
            ->subject("Persediaan " . $this->user['name'] . ", Menipis!")
            ->view('emails.low-stock')
            ->with([
                    "user" => $this->user,
                    "stocks" => $this->stocks
                ]);
    }
}

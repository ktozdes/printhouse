<?php

namespace App\Listeners;

use App\Events\PaymentWasCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


use App\User;
use App\Payment;

class UpdateUserBalance
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaymentWasCreated  $event
     * @return void
     */
    public function handle(PaymentWasCreated $event)
    {
        User::find( $event->payment['user_id'] )->increment('balance', $event->payment['amount']);
    }
}

<?php

namespace App\Listeners;

use App\Events\PaymentWasCreated;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


use App\Payment;
use App\Order;
use App\User;

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
        
        $payment = Payment::create([
            'amount'        => $event->data['amount'],
            'name'          => $event->data['name'],
            'balance_before'=> $event->data['user']->balance,
            'balance_after' => $event->data['user']->balance + $event->data['amount'],
            'user_id'       => $event->data['user']->id,
            'manager_id'    => $event->data['manager']->id,
        ]);
        

        if (isset($event->data['order_id'])) {
            $order = Order::find($event->data['order_id']);
            $order->payment_id = $payment->id;
            $order->save();
        }

        User::find( $event->data['user']['id'] )->increment('balance', $event->data['amount']);
    }
}

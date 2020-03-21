<?php

namespace App\Listeners;

use App\Events\PaymentWasCreated;
use App\Events\PlateQuantityChanged;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


use App\User;
use App\Payment;
use App\Order;
use App\Plate;
use App\File;

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
        $price = 0;
        if (isset($event->data['order'])) {
            $tmpPrice = $this->calculateOrderPrice([
                'plate_id'  => $event->data['plate_id'],
                'file_id'  => $event->data['file_id'],
                'c'         => $event->data['order']['c'],
                'm'         => $event->data['order']['m'],
                'y'         => $event->data['order']['y'],
                'k'         => $event->data['order']['k'],
                'pantone'   => $event->data['order']['pantone'],
            ], $event->data['user']);

            $price = (-1) * $tmpPrice;
        }
        else {
            $price = $event->data['amount'];
        }
        

        if (isset($event->data['payment_id'])){
            $payment = Payment::find($event->data['payment_id']);
            $previousPrice = $payment->amount;
            
            $payment->amount = $price;
            $payment->name = $event->data['name']. ' b:'. $event->data['user']->balance. ' p:' . $price.' pp:'. $previousPrice;
            $payment->balance_before = $event->data['user']->balance - $previousPrice;
            $payment->balance_after = ($event->data['user']->balance - $previousPrice) + $price;
            $payment->user_id       =  $event->data['user']->id;
            $payment->manager_id    =  $event->data['manager']->id;
            $payment->save();

            //updating user balance
            $price = $price - $previousPrice;
        }
        else{
            $payment = new Payment([
                'amount'        => $price,
                'name'          => $event->data['name'],
                'balance_before'=> $event->data['user']->balance,
                'balance_after' => $event->data['user']->balance + $price,
                'user_id'       => $event->data['user']->id,
                'manager_id'    => $event->data['manager']->id,
            ]);
            $payment->save();

            if (isset($event->data['order'])) {
                $order = Order::find($event->data['order']['id']);
                $order->payment_id = $payment->id;
                $order->save();
            }
        }

        User::find( $event->data['user']['id'] )->increment('balance', $price);
    }

    private function calculateOrderPrice($args, $user) {
        $file = File::find($args['file_id']);
        $plate = Plate::find($args['plate_id']);
        $price = $plate->price;
        foreach ($user->pricing as $key => $platePrice) {
            if ($platePrice['plate_id'] == $args['plate_id']) {
                $price = $platePrice['price'];
            }
        }
        $selectedColors = count(array_filter([
            $args['c'],
            $args['m'],
            $args['y'],
            $args['k'],
            $args['pantone'],
        ]));
        return ($price * $file->pages * $selectedColors);
    }
}

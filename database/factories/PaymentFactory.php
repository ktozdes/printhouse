<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Payment;
use App\User;
use App\Order;
use App\Storage;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker, $params) {
    $payment = [];
    if ($params['name'] == 'payment') {
        $user = User::all()->random();
        $date = $faker->dateTimeBetween('-2 years');
        $amount = $faker->numberBetween(10000, 20000);
        $payment = [
            'amount' => $amount,
            'name' => 'payment',
            'balance_before' => $user->balance,
            'balance_after' => $user->balance + $amount,
            'user_id' => $user->id,
            'manager_id' =>1,
            'created_at' =>$date,
            'updated_at' =>$date
        ];
        $user->balance = $user->balance + $amount;
        $user->save();
    }
    else {
        $order = Order::find($params['manager_id']);
        $storage = Storage::where('order_id' , $order->id)->first();
        $user = User::find($order->user_id);
        $selectedColors = count(array_filter([
            $order->c,
            $order->m,
            $order->y,
            $order->k,
            $order->pantone,
        ]));
        $amount = (-1) * $order->pdf->pages * $selectedColors * $storage->price;
        unset($params['manager_id']);
        $payment = [
            'amount' => $amount,
            'name' => 'order',
            'balance_before' => $user->balance,
            'balance_after' => $user->balance + $amount,
            'user_id' =>$user->id,
            'manager_id' =>1,
            'created_at' =>$order->created_at,
            'updated_at' =>$order->created_at
        ];
        $user->balance = $user->balance + $amount;
        $user->save();
    }
    return $payment;
});
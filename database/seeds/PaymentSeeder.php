<?php

use Illuminate\Database\Seeder;
use App\Order;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('payments')->truncate();
        factory('App\Payment', 8)->create(['name' => 'payment']);
        Order::where('status_id', 3)->each(function ($order){
            $payment = factory('App\Payment')->create(['name' => 'order', 'comment'=> $order->id]); 
            $order->payment_id = $payment->id;
            $order->save();
        });
    }
}

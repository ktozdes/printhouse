<?php

use Illuminate\Database\Seeder;
use App\Order;

class StorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('storages')->truncate();
        factory('App\Storage', 25)->create(['name' => 'storage']);
        Order::all()->each(function ($order){
            factory('App\Storage')->create(['name' => 'order', 'order_id'=> $order->id]); 
        });
    }
}

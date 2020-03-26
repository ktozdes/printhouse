<?php

use Illuminate\Database\Seeder;
use App\Order;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('files')->truncate();
        Order::all()->each(function ($order){
            factory('App\File')->create(['filable_id'=> $order->id]); 
        });
    }
}

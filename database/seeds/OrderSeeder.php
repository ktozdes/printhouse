<?php

use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('orders')->truncate();
        for ($i = 0; $i < 100 ;$i ++) {
            factory('App\Order')->create(['comment'=> $i]);
        }
        
    }
}

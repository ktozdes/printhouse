<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PlateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('plates')->truncate();
        DB::table('plates')->insert([
        	['name' => '335_485', 'quantity' => 0, 'price'=>150, 'created_at' => Carbon::now()],
        	['name' => '510_400', 'quantity' => 0, 'price'=>155, 'created_at' => Carbon::now()],
        	['name' => '720_557', 'quantity' => 0, 'price'=>160, 'created_at' => Carbon::now()],
        	['name' => '724_557', 'quantity' => 0, 'price'=>165, 'created_at' => Carbon::now()],
        	['name' => '740_605', 'quantity' => 0, 'price'=>170, 'created_at' => Carbon::now()],
        	['name' => '745_605', 'quantity' => 0, 'price'=>175, 'created_at' => Carbon::now()],
            ['name' => '1030_770', 'quantity' => 0, 'price'=>180, 'created_at' => Carbon::now()],
            ['name' => '1030_785', 'quantity' => 0, 'price'=>185, 'created_at' => Carbon::now()],
            ['name' => '1030_790', 'quantity' => 0, 'price'=>190, 'created_at' => Carbon::now()],
        ]
        );
    }
}

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
        	['name' => 'A6', 'quantity' => 0, 'created_at' => Carbon::now()],
        	['name' => 'A5', 'quantity' => 0, 'created_at' => Carbon::now()],
        	['name' => 'A4', 'quantity' => 0, 'created_at' => Carbon::now()],
        	['name' => 'A3', 'quantity' => 0, 'created_at' => Carbon::now()],
        	['name' => 'A2', 'quantity' => 0, 'created_at' => Carbon::now()],
        	['name' => 'A1', 'quantity' => 0, 'created_at' => Carbon::now()]
        ]
        );
    }
}

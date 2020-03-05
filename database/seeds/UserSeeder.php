<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        DB::table('users')->insert([
        	[
        		'name' => 'chyngyz', 
        		'fullname' => 'Sydykov Chyngyz', 
        		'company' => 'Developer', 
        		'phone1' => '0555-944-645', 
        		'address' => 'туголбай ата 67 кв 114',
        		'email' => 'chyngyz6@gmail.com',
        		'password' => Hash::make('pass'),
        		'api_token' => Str::random(60),
        		'created_at' => Carbon::now()
        	],
        	[
        		'name' => 'client', 
        		'fullname' => 'Clientov Client', 
        		'company' => 'Client', 
        		'phone1' => '0555-944-655', 
        		'address' => 'Bishkek ата 110 кв 22',
        		'email' => 'client6@local.loc',
        		'password' => Hash::make('pass'),
        		'api_token' => Str::random(60),
        		'created_at' => Carbon::now()
        	],
        ]);
    }
}

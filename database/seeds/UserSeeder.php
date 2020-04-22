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
        		'address' => 'Tуголбай ата 67 кв 114',
                'balance' => '10000',
        		'email' => 'chyngyz6@gmail.com',
        		'password' => Hash::make('pass'),
        		'api_token' => Str::random(60),
        	],
            [
                'name' => 'manager', 
                'fullname' => 'Managerov Manager', 
                'company' => 'Developer', 
                'phone1' => '0555-001-000', 
                'address' => 'Tуголбай ата 111',
                'balance' => '5000',
                'email' => 'manager@local.loc',
                'password' => Hash::make('pass'),
                'api_token' => Str::random(60),
            ],
            [
                'name' => 'manager2', 
                'fullname' => 'Managerov Manager2', 
                'company' => 'Developer', 
                'phone1' => '0555-002-000', 
                'address' => 'Tуголбай ата 112',
                'balance' => '5000',
                'email' => 'manager2@local.loc',
                'password' => Hash::make('pass'),
                'api_token' => Str::random(60),
            ],
        	[
        		'name' => 'client', 
        		'fullname' => 'Clientov Client', 
        		'company' => 'Client', 
        		'phone1' => '0555-000-001', 
        		'address' => 'Bishkek ата 110 кв 22',
                'balance' => '-50',
        		'email' => 'client@local.loc',
        		'password' => Hash::make('pass'),
        		'api_token' => Str::random(60)
        	],
            [
                'name' => 'client2', 
                'fullname' => 'Clientov Client', 
                'company' => 'Client', 
                'phone1' => '0555-000-002', 
                'address' => 'city Moscow, Mother Russia 2',
                'balance' => '-5000',
                'email' => 'client2@local.loc',
                'password' => Hash::make('pass'),
                'api_token' => Str::random(60)
            ],
            [
                'name' => 'client3', 
                'fullname' => 'Shalom Client3', 
                'company' => 'Client3', 
                'phone1' => '0555-000-003', 
                'address' => 'city Moscow, New York',
                'balance' => '5000',
                'email' => 'client3@local.loc',
                'password' => Hash::make('pass'),
                'api_token' => Str::random(60)
            ],[
                'name' => 'client4', 
                'fullname' => 'Shalom Client4', 
                'company' => 'Client3', 
                'phone1' => '0555-000-004', 
                'address' => 'city Moscow, New York',
                'balance' => '5000',
                'email' => 'client4@local.loc',
                'password' => Hash::make('pass'),
                'api_token' => Str::random(60)
            ],
        ]);
    }
}

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Payment;
use App\User;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker) {
	$amount = $faker->numberBetween(-10000,10000);
	$name = ($amount < 0) ? 'payment' : 'order';
    $date = $faker->dateTimeBetween('-2 years');
	//'amount', 'comment', 'user_id', 'name', 'balance_before', 'balance_after',
    return [
        'amount' => $amount,
        'name' => $name,
        'balance_before' => $faker->numberBetween(-100000,100000),
        'balance_after' => $faker->numberBetween(-100000,100000),
        'user_id' =>User::all()->random()->id,
        'manager_id' =>1,
        'created_at' =>$date,
        'updated_at' =>$date
    ];
});
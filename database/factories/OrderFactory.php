<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use App\User;
use App\Plate;
use App\Status;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'c' => $faker->boolean($chanceOfGettingTrue = 50),
        'm' => $faker->boolean($chanceOfGettingTrue = 50),
        'y' => $faker->boolean($chanceOfGettingTrue = 50),
        'k' => $faker->boolean($chanceOfGettingTrue = 50),
        'pantone' => $faker->boolean($chanceOfGettingTrue = 10),
        'urgent' => $faker->boolean($chanceOfGettingTrue = 10),
        'deliver' => $faker->boolean($chanceOfGettingTrue = 70),
        'address' => $faker->address,
        'quantity' => $faker->randomDigit,
        'price' => $faker->randomFloat(2, $min = 0, $max = 1000),
        'user_id' =>User::all()->random()->id,
        'plate_id' =>Plate::all()->random()->id,
        'status_id' =>Status::all()->random()->id,
    ];
});
<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use App\User;
use App\Plate;
use App\Status;
use App\Payment;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker,$params) {
    $now = Carbon\Carbon::now()->subMonths(4);
    $date = $now->add($params['comment'], 'day');
    return [
        'c' => $faker->boolean($chanceOfGettingTrue = 50),
        'm' => $faker->boolean($chanceOfGettingTrue = 50),
        'y' => $faker->boolean($chanceOfGettingTrue = 50),
        'k' => $faker->boolean($chanceOfGettingTrue = 50),
        'pantone' => $faker->boolean($chanceOfGettingTrue = 10),
        'urgent' => $faker->boolean($chanceOfGettingTrue = 10),
        'deliver' => $faker->boolean($chanceOfGettingTrue = 70),
        'address' => $faker->address,
        'user_id' => User::all()->random()->id,
        'manager_id' => User::all()->random()->id,
        'status_id'  => Status::all()->random()->id,
        'created_at' =>$date,
        'updated_at' =>$date
    ];
});
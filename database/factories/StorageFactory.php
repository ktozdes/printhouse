<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Storage;
use App\Plate;
use Faker\Generator as Faker;

$factory->define(Storage::class, function (Faker $faker) {
    $date = $faker->dateTimeBetween('-2 years');
    $price = round($faker->numberBetween(-10000,10000), -2);
    return [
        'quantity' => $faker->numberBetween(10,100),
        'price' => $price,
        'plate_id' => Plate::all()->random()->id,
        'manager_id' =>User::all()->random()->id,
        'created_at' =>$date,
        'updated_at' =>$date
    ];
});

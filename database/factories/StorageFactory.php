<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Storage;
use App\Plate;
use Faker\Generator as Faker;

$factory->define(Storage::class, function (Faker $faker) {
    $date = $faker->dateTimeBetween('-2 years');
    $price = round($faker->numberBetween(0,10000), -2);

    $quantity = $faker->numberBetween(-10,10);
    $name = ($quantity < 0) ? 'order' : 'store';

    return [
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'quantity_before' => $quantity-1,
        'quantity_after' => $quantity+1,
        'plate_id'   => Plate::all()->random()->id,
        'user_id'    => User::all()->random()->id,
        'manager_id' => User::all()->random()->id,
        'created_at' =>$date,
        'updated_at' =>$date
    ];
});

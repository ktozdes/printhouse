<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\File;
use App\Order;
use Faker\Generator as Faker;

$factory->define(File::class, function (Faker $faker, $params) {
	$order = Order::find($params['filable_id']);
	$date = $order->created_at;
    $pages = $faker->numberBetween(3,20);
    $name = $faker->numberBetween(1,5) . 'bet.pdf';
    $oldName = $order->id.'---'.$faker->numberBetween(1,5) . 'bet.pdf';
    return [
        'url' => 'http://localhost/printhouse/public/uploads/pdf/' . $name,
        'name' => $name,
        'old_name' => $oldName,
        'pages' => $pages,
        'filable_id' => $order->id,
        'filable_type'   =>  'App\Order',
        
        'created_at' =>$date,
        'updated_at' =>$date
    ];
}); 
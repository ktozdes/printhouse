<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Storage;
use App\Order;
use App\Plate;
use Faker\Generator as Faker;

$factory->define(Storage::class, function (Faker $faker, $params) {
    $date = $faker->dateTimeBetween('-2 years');
    $plate = Plate::all()->random();
    $storage = [];
    if ($params['name'] == 'storage') {
        $quantity = $faker->numberBetween(50,100);
        $price = round($faker->numberBetween(40,80), -1);
        $plateQuantity = $quantity;
        $storage = [
            'name' => $params['name'],
            'price' => $price,
            'quantity' => $quantity,
            'global_quantity_before' => $plate->quantity,
            'global_quantity_after' => $plate->quantity + $quantity,
            'plate_id'   =>  $plate->id,
            'user_id'    => User::all()->random()->id,
            'manager_id' => User::all()->random()->id,
            
            'created_at' =>$date,
            'updated_at' =>$date
        ];
        $plate->quantity = $plate->quantity + $quantity;
        $plate->update();
    }
    else if ($params['name'] == 'order') {

        $order = Order::find($params['order_id']);
        $quantity = $order->pdf->pages;

        $price = round($faker->numberBetween(150,300), -1);
        //selects last inputed storages
        $inputStorages = 
        DB::table('storages as inp')
        ->select(DB::raw("inp.plate_id, inp.id, inp.quantity, inp.updated_at, min_value"))
        ->leftJoinSub(
            DB::table('storages')
            ->select(DB::raw("used_storage_id, min(local_quantity_after) as min_value"))
            ->groupBy('used_storage_id'), 
            'outp', function($join) {
            $join->on('inp.id', '=', 'outp.used_storage_id');
        })
        ->where([
            ['inp.name', 'storage'],
            ['plate_id', $plate->id],
        ])
        ->where(function($q) {
            $q->where('min_value', '>', 0)
            ->orWhereNull('min_value');
        })
        ->orderBy('inp.updated_at', 'asc')
        ->get();
        if (count($inputStorages) > 0) {
            //decrementing from very first input storage
            if (is_null($inputStorages[0]->min_value)) {
                $storage = [
                    'name' => $params['name'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'global_quantity_before' => $plate->quantity,
                    'global_quantity_after' => $plate->quantity - $quantity,
                    'local_quantity_before' => $inputStorages[0]->quantity,
                    'local_quantity_after' =>  $inputStorages[0]->quantity - $quantity,
                    'used_storage_id' => $inputStorages[0]->id,
                    'plate_id'   =>  $plate->id,
                    
                    'user_id'   => $order->user_id,
                    'manager_id' => $order->manager_id,
                    'order_id' => $order->id,

                    'created_at' =>$date,
                    'updated_at' =>$date
                ];
            }
            //decrementing from current input storage
            else if (is_numeric($inputStorages[0]->min_value) && $inputStorages[0]->min_value > $quantity){

                $storage = [
                    'name' => $params['name'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'global_quantity_before' => $plate->quantity,
                    'global_quantity_after' => $plate->quantity - $quantity,
                    'local_quantity_before' => $inputStorages[0]->min_value,
                    'local_quantity_after' =>  $inputStorages[0]->min_value - $quantity,
                    'used_storage_id' => $inputStorages[0]->id,
                    'plate_id'   =>  $plate->id,
                    
                    'user_id'   => $order->user_id,
                    'manager_id' => $order->manager_id,
                    'order_id' => $order->id,
                    
                    'created_at' =>$date,
                    'updated_at' =>$date
                ];
            }
            //decrementing from current input and passing to the next storage
            else if (isset($inputStorages[1]) && is_numeric($inputStorages[0]->min_value) && $inputStorages[0]->min_value < $quantity) {
                Storage::create([
                    'name' => $params['name'],
                    'price' => $price,
                    'quantity' => $inputStorages[0]->min_value,
                    'global_quantity_before' => $plate->quantity,
                    'global_quantity_after' => $plate->quantity - $inputStorages[0]->min_value,
                    'local_quantity_before' => $inputStorages[0]->min_value,
                    'local_quantity_after' =>  0,
                    'used_storage_id' => $inputStorages[0]->id,
                    'plate_id'   =>  $plate->id,
                    
                    'user_id'   => $order->user_id,
                    'manager_id' => $order->manager_id,
                    'order_id' => $order->id,

                    'created_at' =>$date,
                    'updated_at' =>$date
                ]);
                $storage = [
                    'name' => $params['name'],
                    'price' => $price,
                    'quantity' => $quantity - $inputStorages[0]->min_value,
                    'global_quantity_before' => $plate->quantity - $inputStorages[0]->min_value,
                    'global_quantity_after' => ($plate->quantity - $inputStorages[0]->min_value) - ($quantity - $inputStorages[0]->min_value),
                    'local_quantity_before' => $inputStorages[1]->quantity,
                    'local_quantity_after' =>  $inputStorages[1]->quantity - ($quantity - $inputStorages[0]->min_value),
                    'used_storage_id' => $inputStorages[1]->id,
                    'plate_id'   =>  $plate->id,
                    
                    'user_id'   => $order->user_id,
                    'manager_id' => $order->manager_id,
                    'order_id' => $order->id,
                    
                    'created_at' =>$date,
                    'updated_at' =>$date
                ];
            }
            //created new input if output is bigger than global storage
            else if (!isset($inputStorages[1]) && is_numeric($inputStorages[0]->min_value) && $inputStorages[0]->min_value < $quantity) {
                $quickQuantity = $faker->numberBetween(50,100);
                $plateQuantity = $quantity;
                $quickInput = Storage::create([
                    'name' => 'storage',
                    'price' => round($faker->numberBetween(40,80), -1),
                    'quantity' => $quickQuantity,
                    'global_quantity_before' => $plate->quantity,
                    'global_quantity_after' => $plate->quantity + $quickQuantity,
                    'plate_id'   =>  $plate->id,
                    'user_id'    => User::all()->random()->id,
                    'manager_id' => User::all()->random()->id,
                    
                    'created_at' =>$date,
                    'updated_at' =>$date
                ]);
                $plate->quantity = $plate->quantity + $quickQuantity;
                $plate->update();

                Storage::create([
                    'name' => $params['name'],
                    'price' => $price,
                    'quantity' => $inputStorages[0]->min_value,
                    'global_quantity_before' => $plate->quantity,
                    'global_quantity_after' => $plate->quantity - $inputStorages[0]->min_value,
                    'local_quantity_before' => $inputStorages[0]->min_value,
                    'local_quantity_after' =>  0,
                    'used_storage_id' => $inputStorages[0]->id,
                    'plate_id'   =>  $plate->id,
                    
                    'user_id'   => $order->user_id,
                    'manager_id' => $order->manager_id,
                    'order_id' => $order->id,

                    'created_at' =>$date,
                    'updated_at' =>$date
                ]);

                $storage = [
                    'name' => $params['name'],
                    'price' => $price,
                    'quantity' => $quantity - $inputStorages[0]->min_value,
                    'global_quantity_before' => $plate->quantity - $inputStorages[0]->min_value,
                    'global_quantity_after' => ($plate->quantity - $inputStorages[0]->min_value) - ($quantity - $inputStorages[0]->min_value),
                    'local_quantity_before' => $quickInput->quantity,
                    'local_quantity_after' =>  $quickInput->quantity - ($quantity - $inputStorages[0]->min_value),
                    'used_storage_id' => $quickInput->id,
                    'plate_id'   =>  $plate->id,
                    
                    'user_id'   => $order->user_id,
                    'manager_id' => $order->manager_id,
                    'order_id' => $order->id,
                    
                    'created_at' =>$date,
                    'updated_at' =>$date
                ];


            }
            if ($order->status_id == 3) {
                $plate->quantity = $plate->quantity - $quantity;
                $plate->update();
            }
        }
    }
    return $storage;
});

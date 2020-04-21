<?php

namespace App\Listeners;

use App\Events\PlateQuantityChanged;
use App\Events\PaymentWasCreated;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Storage;
use App\Plate;
use App\User;
use Illuminate\Support\Facades\DB;

class UpdatePlateQuantity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PlateQuantityChanged  $event
     * @return void
     */
    public function handle(PlateQuantityChanged $event)
    {
        if (isset($event->data['order_id'])) {
            $pricing = DB::table('orders')
            ->select(DB::raw('orders.id, orders.user_id as user_id, storage.storage_id, storage.plate_id, plate.quantity as plate_quantity, plate.price as plate_price, plate_user.price as user_price'))
            ->leftJoinSub(
                DB::table('storages')
                ->select(DB::raw("id as storage_id, order_id, plate_id as plate_id"))
                ->where('order_id', $event->data['order_id'])
                ->offset(0)->take(1),
                'storage', function($join) {
                $join->on('storage.order_id', '=', 'orders.id');
            })
            ->leftJoin('plates as plate', function($plateQuery) {
                $plateQuery->on('plate.id', '=', 'storage.plate_id');
            })
            ->leftJoin('plate_user', function($plateUserQuery) {
                $plateUserQuery->on('plate_user.plate_id', '=', 'storage.plate_id');
                $plateUserQuery->on('plate_user.user_id', '=', 'orders.user_id');
            })
            ->where('order_id', $event->data['order_id'])
            ->first();
        }
        else if (isset($event->data['plate_id'])) {
            $pricing = new \stdClass();
            $pricing->storage_id = null;
            $pricing->plate_id = $event->data['plate_id'];
            $pricing->user_id = $event->data['manager_id'];
            $pricing->user_price = 'null';
            $pricing->plate_price = Plate::find($event->data['plate_id'])->price;
        }

        $inputStorages = DB::table('storages as inp')
        ->select(DB::raw("inp.id as id, inp.plate_id, inp.quantity, inp.updated_at, min_value"))
        ->leftJoinSub(
            DB::table('storages')
            ->select(DB::raw("used_storage_id, min(local_quantity_after) as min_value"))
            ->groupBy('used_storage_id'), 
            'outp', function($join) {
            $join->on('inp.id', '=', 'outp.used_storage_id');
        })
        ->where([
            ['inp.name', 'storage'],
            ['plate_id', $pricing->plate_id],
        ])
        ->where(function($q) {
            $q->where('min_value', '>', 0)
            ->orWhereNull('min_value');
        })
        ->orderBy('inp.updated_at', 'asc')
        ->get();

        $update = true;

        $quantity = $event->data['quantity'];
        $name = isset($event->data['name']) ? $event->data['name'] : 'order';
        $comment = isset($event->data['comment']) ? $event->data['comment'] : null;
        $orderPrice = is_numeric($pricing->user_price) ? $pricing->user_price : $pricing->plate_price;

        foreach ($inputStorages as $key => $inputStorage) {
            if ($quantity > 0) {
                if (is_numeric($inputStorage->min_value)) {
                    if ($inputStorage->min_value >= $quantity) {
                        $this->updateInsertStorage([
                            'quantity' => $quantity,
                            'name' => $name,
                            'storage_id' => ($update) ? $pricing->storage_id : null,
                            'price' => $orderPrice,
                            'comment' => $comment,
                            'local_quantity' => $inputStorage->min_value,
                            'used_storage_id' => $inputStorage->id,
                            'order_id' => $event->data['order_id'],
                            'plate_id' => $pricing->plate_id,
                            'user_id'  => $pricing->user_id,
                            'manager_id'  => $event->data['manager_id'],
                        ]);
                        $update = false;
                        $quantity = 0;
                    }
                    else if ($inputStorage->min_value < $quantity) {
                        $this->updateInsertStorage([
                            'quantity' => $inputStorage->min_value,
                            'name' => $name,
                            'storage_id' => ($update) ? $pricing->storage_id : null,
                            'price' => $orderPrice,
                            'comment' => $comment,
                            'local_quantity' => $inputStorage->min_value,
                            'used_storage_id' => $inputStorage->id,
                            'order_id' => $event->data['order_id'],
                            'plate_id' => $pricing->plate_id,
                            'user_id'  => $pricing->user_id,
                            'manager_id'  => $event->data['manager_id'],
                        ]);
                        $update = false;
                        $quantity = $quantity - $inputStorage->min_value;
                    }
                }
                else {
                    if ($inputStorage->quantity >= $quantity) {
                        $this->updateInsertStorage([
                            'quantity' => $quantity,
                            'name' => $name,
                            'storage_id' => ($update) ? $pricing->storage_id : null,
                            'price' => $orderPrice,
                            'comment' => $comment,
                            'local_quantity' => $inputStorage->quantity,
                            'used_storage_id' => $inputStorage->id,
                            'order_id' => $event->data['order_id'],
                            'plate_id' => $pricing->plate_id,
                            'user_id'  => $pricing->user_id,
                            'manager_id'  => $event->data['manager_id'],
                        ]);
                        $update = false;
                        $quantity = 0;
                    }
                    else if ($inputStorage->quantity < $quantity) {
                        $this->updateInsertStorage([
                            'quantity' => $inputStorage->quantity,
                            'name' => $name,
                            'storage_id' => ($update) ? $pricing->storage_id : null,
                            'price' => $orderPrice,
                            'comment' => $comment,
                            'local_quantity' => $inputStorage->quantity,
                            'used_storage_id' => $inputStorage->id,
                            'order_id' => $event->data['order_id'],
                            'plate_id' => $pricing->plate_id,
                            'user_id'  => $pricing->user_id,
                            'manager_id'  => $event->data['manager_id'],
                        ]);
                        $update = false;
                        $quantity = $quantity - $inputStorage->quantity;
                    }
                }
            }
        }
        if (isset($event->data['order_id'])) {
            event(new PaymentWasCreated([
                'name'      => 'order',
                'order_id'  => $event->data['order_id'],
                'amount'    => ((-1) * $orderPrice * $event->data['quantity']),
                'user'      => User::find($pricing->user_id),
                'manager'   => User::find($event->data['manager_id']),
            ]));
        }
        else if (isset($event->data['name']) && $event->data['name'] == 'defect') {
            event(new PaymentWasCreated([
                'name'      => 'defect',
                'amount'    => ((-1) * $orderPrice * $event->data['quantity']),
                'user'      => User::find($event->data['manager_id']),
                'manager'   => User::find($event->data['manager_id']),
            ]));
        }
    }

    private function updateInsertStorage($storage) {
        $plate = Plate::find($storage['plate_id']);
        
        $storage = Storage::updateOrCreate(
            ['id' => $storage['storage_id']],
            [
                'quantity'         => $storage['quantity'],
                'name'             => $storage['name'],
                'price'            => $storage['price'],
                'comment'          => $storage['comment'],
                'order_id'         => $storage['order_id'],
                'plate_id'         => $storage['plate_id'],
                'user_id'          => $storage['user_id'],
                'manager_id'       => $storage['manager_id'],
                'used_storage_id'  => $storage['used_storage_id'],

                'global_quantity_before'   => $plate->quantity,
                'global_quantity_after'    => $plate->quantity - $storage['quantity'],
                'local_quantity_before'    => $storage['local_quantity'],
                'local_quantity_after'     => $storage['local_quantity'] - $storage['quantity'],
            ]
        );
        
        $plate->decrement('quantity', $storage['quantity']);
    }
}

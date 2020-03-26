<?php

namespace App\Http\Controllers;

use App\Order;
use App\File;
use App\Plate;
use App\Payment;
use App\Storage;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Events\PaymentWasCreated;
use App\Events\PlateQuantityChanged;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $uploadsPath;
 
    public function __construct()
    {
        $this->uploadsPath = '/uploads/pdf';
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        if (!$request->user->hasRole('client')) {
            $orders = Order::select([
                'orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'orders.address', 'orders.comment', 
                'status.id as status_id', 'status.name as status_name', 
                'file.id as file_id', 'file.old_name as file_name', 'file.url as file_url', 'file.pages as pages',
                'user.id as user_id', 'user.name as user_name', 
                'manager.id as manager_id', 'manager.name as manager_name',

                'storage.id as storage_id', 'storage.quantity as quantity', 'storage.plate_id as plate_id', 
                'plate.name as plate_name', 'plate.price as plate_price', 'plate.quantity as plate_quantity',
                'plate_user.price as user_price',
            ])
            ->join('users as user', 'user.id', '=', 'orders.user_id')
            ->join('users as manager', 'manager.id', '=', 'orders.manager_id')
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->leftJoinSub(
                DB::table('storages')
                ->select(DB::raw("order_id, max(id) as id, count(id) as storage_number, AVG(plate_id) as plate_id, sum(quantity) as quantity"))
                ->groupBy('order_id'), 
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
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->groupBy('file.filable_id');
            })
            ->orderBy('orders.id', 'desc')
            ->paginate(25);
        }
        else{
            $orders = Order::select([
                'orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'orders.address', 'orders.comment', 
                'status.id as status_id', 'status.name as status_name', 
                'file.id as file_id', 'file.old_name as file_name', 'file.url as file_url', 'file.pages as pages',
                'user.id as user_id', 'user.name as user_name', 
                'manager.id as manager_id', 'manager.name as manager_name',

                'storage.id as storage_id', 'storage.quantity as quantity', 'storage.plate_id as plate_id', 
                'plate.name as plate_name', 'plate.price as plate_price', 'plate.quantity as plate_quantity',
                'plate_user.price as user_price',
            ])
            ->join('users as user', 'user.id', '=', 'orders.user_id')
            ->join('users as manager', 'manager.id', '=', 'orders.manager_id')
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->leftJoinSub(
                DB::table('storages')
                ->select(DB::raw("order_id, max(id) as id, count(id) as storage_number, AVG(plate_id) as plate_id, sum(quantity) as quantity"))
                ->groupBy('order_id'), 
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
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->groupBy('file.filable_id');
            })
            ->where('orders.user_id', $request->user->id)
            ->orderBy('orders.id', 'desc')
            ->paginate(25);
        }

        foreach($orders as $key => $order){
            $orders[$key] = $this->normalizeItem($order);
        }
        
        return response()->json([
            'status' => 'success',
            'orders' => $orders,
            'message' => 'Список Заказов выбран.',
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'order.file.id'=>'required|integer',
            'order.storage.plate_id'=>'required|integer'
        ]);
        $order = new Order($request->order);
        $user = (isset($request->order['user_id'])) ? User::find($request->order['user_id']) : $request->user;
        $order->user_id = $user->id;
        $order->manager_id = $request->user->id;
        $order->status_id = 1;
        $order->save();

        $file = File::find($request->order['file']['id']);
        $file->filable_id = $order->id;
        $file->filable_type = 'App\Order';
        $file->save();
        
        $selectedColors = count(array_filter([
            $request->order['c'],
            $request->order['m'],
            $request->order['y'],
            $request->order['k'],
            $request->order['pantone'],
        ]));

        $storage = new Storage([
            'name' => 'order',
            'quantity' => $selectedColors * $file->pages,
            'order_id' => $order->id,
            'plate_id' => $request->order['storage']['plate_id'],
            'user_id' => $user->id,
            'manager_id' => $request->user->id,
        ]);

        $storage->save();

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'message' => 'Заказ Создан',
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Order $order)
    {
        $request->validate([
            'id'=>'required|integer',
        ]);
        if ($request->user->hasRole('client')) {
            $order = Order::select(['orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'address', 'orders.comment as comment', 'status_id', 'orders.user_id as user_id', 
                'file.pages as pages',  
                'storage.plate_id as plate_id', 'storage.id as storage_id',
            ])
            ->leftJoinSub(
                DB::table('storages')
                ->select(DB::raw("order_id, max(id) as id, max(plate_id) as plate_id"))
                ->groupBy('order_id'), 
                'storage', function($join) {
                    $join->on('storage.order_id', '=', 'orders.id');
            })
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->orderBy('file.name', 'asc');
                $q->groupBy('file.filable_id');
            })
            ->where([
                ['orders.id', '=', $request->id],
                ['status_id', '=', 1],
                ['orders.user_id', '=', $request->user->id],
            ])->first();
        }

        else{
            $order = Order::select(['orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'address', 'orders.comment as comment', 'status_id', 'orders.user_id as user_id',  
                'file.pages as pages',  
                'storage.plate_id as plate_id', 'storage.id as storage_id',
            ])
            ->leftJoinSub(
                DB::table('storages')
                ->select(DB::raw("order_id, max(id) as id, max(plate_id) as plate_id"))
                ->groupBy('order_id'), 
                'storage', function($join) {
                    $join->on('storage.order_id', '=', 'orders.id');
            })
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->orderBy('file.name', 'asc');
                $q->groupBy('file.filable_id');
            })
            ->where([
                ['orders.id', '=', $request->id],
            ])->first();
        }

        $order = $this->normalizeItem($order);
        

        if (empty($order)) {
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя изменить или его нет'], 400);
        }

        $file = File::where([
            ['filable_id', '=', $order->id],
            ['filable_type', '=', 'App\Order'],
        ])->first();
        return response()->json([
            'status' => 'success',
            'order' => $order,
            'file' => $file,
            'message' => 'Заказ найден',
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        
        $request->validate([
            'order.id'=>'required|integer',
            'order.file.id'=>'required|integer',
            'order.storage.plate_id'=>'required|integer'
        ]);

        $order = Order::find($request->order['id']);

        if ( $request->user->hasRole('client') && ($order->user_id != $request->user->id || (
            $order->user_id == $request->user->id && $order->status_id != 1))){
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя изменить'], 403);
        }
        $order->status_id = ($request->user->hasRole('client')) ? 1 : $request->order['status']['id'];
        $order->update($request->order);

        $selectedColors = count(array_filter([
            $order->c,
            $order->m,
            $order->y,
            $order->k,
            $order->pantone,
        ]));

        $storage             = Storage::where('order_id', $order->id)->first();
        $storage->quantity  = $selectedColors * $order->pdf->pages;
        $storage->plate_id   = $request->order['storage']['plate_id'];
        $storage->manager_id = $request->user->id;
        $storage->update();

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'message' => 'Заказ изменен',
        ]);

        
    }/**
     * changes status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request)
    {
        
        $request->validate([
            'order_id'=>'required|integer',
            'status_id'=>'required|integer',
        ]);
        $order = Order::find($request->order_id);
        $order->status_id = $request->status_id;
        $order->save();

        if ($order->status_id == '3' && $request->quantity > 0 ) {

            event(new PlateQuantityChanged([
                'order_id' => $request->order_id,
                'quantity' => $request->quantity,
                'manager_id' => $request->user->id,
            ]));
        }
        return response()->json([
            'status' => 'success',
            'status_id' => $order->status_id,
            'message' => 'Статус заказа изменен',
        ]);

        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Order $order)
    {
        
        $request->validate([
            'id'=>'required|integer',
        ]);
        if ( $request->user->hasRole('client') ) {
            $order = Order::where([
                ['id', '=', $request->id],
                ['status_id', '=', 1],
                ['user_id', '=', $request->user->id],
            ])->first();
        }
        else{
            $order = Order::where([
                ['id', '=', $request->id]
            ])->first();
        }

        $file = File::where([
            ['filable_id', '=', $request->id],
            ['filable_type', '=', 'App\Order'],
        ])->first();

        if (empty($order)) {
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя удалить или его нет'], 403);
        }

        

        if (!empty($order)) {
            User::find( $order->user_id )->increment('balance', - $order->payment->amount);
            $order->payment->delete();
            $order->delete();
        }

        if (!empty($file)) {
            $file_path = public_path($this->uploadsPath) . '/' . $file->name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
     
            if (!empty($file)) {
                $file->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Заказ Удален',
        ]);
    }

    private function normalizeItem( $item ){
        $item['status'] = [
            'id' => $item['status_id'], 
            'name' => $item['status_name'],
        ];
        unset($item['status_name']);

        $item['storage'] = [
            'id' => $item['storage_id'], 
            'quantity' => $item['quantity'],
            'plate_id' => $item['plate_id'],
            'plate' => [
                'id' => $item['plate_id'], 
                'name' => $item['plate_name'],
                'price' => $item['plate_price'],
                'quantity' => $item['plate_quantity'],
                'user_price' => $item['user_price'],
            ]
        ];
        unset($item['storage_id'], $item['quantity']);
        unset($item['plate_id'], $item['plate_name'], $item['plate_price']);
        
        $item['file'] = [
            'id' => $item['file_id'], 
            'name' => $item['file_name'],
            'url' => $item['file_url'],
            'pages' => $item['pages'],
        ];
        unset($item['file_id'], $item['file_name'], $item['file_url'], $item['pages']);

        $item['user'] = [
            'id' => $item['user_id'], 
            'name' => $item['user_name'],
        ];
        unset($item['user_name']);

        $item['manager'] = [
            'id' => $item['manager_id'], 
            'name' => $item['manager_name'],
        ];
        unset($item['manager_id'], $item['manager_name']);
        return $item;
    }
}

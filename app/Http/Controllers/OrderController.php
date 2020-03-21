<?php

namespace App\Http\Controllers;

use App\Order;
use App\File;
use App\Plate;
use App\Payment;
use App\User;
use Illuminate\Http\Request;

use App\Events\PaymentWasCreated;

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
                'storage.id as storage_id', 'storage.quantity as quantity', 'storage.plate_id as plate_id',
                'payment.amount as price', 'payment.id as payment_id',
                'file.id as file_id', 'file.old_name as file_name', 'file.url as file_url', 'file.pages as pages',
                'user.id as user_id', 'user.name as user_name',
                'manager.id as manager_id', 'manager.name as manager_name',
            ])
            ->join('users as user', 'user.id', '=', 'orders.user_id')
            ->join('users as manager', 'manager.id', '=', 'orders.manager_id')
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->join('storages as storage', 'storage.id', '=', 'orders.storage_id')
            ->join('payments as payment', 'payment.id', '=', 'orders.payment_id')
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->orderBy('file.name', 'asc');
                $q->groupBy('file.filable_id');
            })
            ->orderBy('id', 'desc')
            ->paginate(25);
        }
        else{
            $orders = Order::select([
                'orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'orders.address', 'orders.comment', 
                'status_id', 'status.name as status_name', 
                'storage.id as storage_id', 'storage.quantity as quantity', 'storage.plate_id as plate_id',
                'payment.amount as price', 'payment.id as payment_id',
                'file.id as file_id', 'file.old_name as file_name', 'file.url as file_url', 'file.pages as pages',
                'user.id as user_id', 'user.name as user_name',
                'manager.id as manager_id', 'manager.name as manager_name',
            ])
            ->join('users as user', 'user.id', '=', 'orders.user_id')
            ->join('users as manager', 'manager.id', '=', 'orders.manager_id')
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->join('storages as storage', 'storage.id', '=', 'orders.storage_id')
            ->join('payments as payment', 'payment.id', '=', 'orders.payment_id')
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->orderBy('file.name', 'asc');
                $q->groupBy('file.filable_id');
            })
            ->where('user.id', $request->user->id)
            ->orderBy('id', 'desc')
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
        $user = (isset($request->order['user']['id'])) ? User::find($request->order['user']['id']) : $request->user;
        $order->user_id = $user->id;
        $order->manager_id = $request->user->id;
        $order->status_id = 1;
        $order->storage_id = 100;

        $order->save();

        event(new PaymentWasCreated([
            'name'  =>'order',
            'order' => $order,
            'plate_id' => $request->order['storage']['plate_id'],
            'file_id' => $request->order['file']['id'],
            'user'  => $user,
            'manager'  => $request->user,
        ]));

        $file = File::where([
            ['id', '=', $request->order['file']['id']],
        ])->first();
        $file->filable_id = $order->id;
        $file->filable_type = 'App\Order';
        $file->save();

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'file' => $file,
            'response' => $request->order,
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
                'payment.amount as price', 'payment.id as payment_id', 
                'file.pages as pages',  
                'storage.plate_id as plate_id', 'storage.id as storage_id',
            ])
            ->join('storages as storage', 'storage.id', '=', 'orders.storage_id')
            ->join('payments as payment', 'payment.id', '=', 'orders.payment_id')
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
                'payment.amount as price', 'payment.id as payment_id', 
                'file.pages as pages',  
                'storage.plate_id as plate_id', 'storage.id as storage_id',
            ])
            ->join('storages as storage', 'storage.id', '=', 'orders.storage_id')
            ->join('payments as payment', 'payment.id', '=', 'orders.payment_id')
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

        //print_r($order);

        $order = $this->normalizeItem($order);
        
        //print_r($order);
        //die;
        

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
        $user = (isset($request->order['user']['id'])) ? User::find($request->order['user']['id']) : $request->user;
        $order->status_id = ($request->user->hasRole('client')) ? 1 : $request->order['status']['id'];
        $order->update($request->order);
        $order->save();

        event(new PaymentWasCreated([
            'name'  =>'update order',
            'order' => $order,
            'payment_id' => $order->payment_id,
            'plate_id' => $request->order['storage']['plate_id'],
            'file_id' => $request->order['file']['id'],
            'user'  => $user,
            'manager'  => $request->user,
        ]));

        return response()->json([
            'status' => 'success',
            'order' => $order,
            //'payment' => $payment,
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

        if (!empty($file)) {
            $file_path = public_path($this->uploadsPath) . '/' . $file->name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
     
            if (!empty($file)) {
                $file->delete();
            }
        }
        

        if (!empty($order)) {
            $order->delete();
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

        $item['payment'] = [
            'id' => $item['payment_id'],
            'price' => $item['price'],
        ];
        unset($item['payment_id'], $item['price']);

        $item['storage'] = [
            'id' => $item['storage_id'], 
            'quantity' => $item['quantity'],
            'plate_id' => $item['plate_id'],
        ];
        unset($item['storage_id'], $item['quantity'], $item['plate_id']);
        
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

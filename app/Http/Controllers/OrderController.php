<?php

namespace App\Http\Controllers;

use App\Order;
use App\File;
use App\Plate;
use App\Payment;
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
    public function store(Request $request)
    {
        $request->validate([
            'file_id'=>'required|integer',
            'order.plateId'=>'required|integer'
        ]);
        $order = new Order($request->order);
        $order->user_id = $request->user->id;
        $order->status_id = 1;
        $order->plate_id = $request->order['plateId'];
        $order->quantity = $request->order['quantity'];

        $order->price = $this->calculateOrderPrice([
            'plate_id'=>$request->order['plateId'],
            'quantity'=>$request->order['quantity'],
            'c'=>$request->order['c'],
            'm'=>$request->order['m'],
            'y'=>$request->order['y'],
            'k'=>$request->order['k'],
            'pantone'=>$request->order['pantone'],
        ], $request->user);

        $payment = new Payment([
            'amount'=> (-1) * $order->price,
            'name'=>'order',
            'balance_before'=>$request->user['balance'],
            'balance_after'=>( $request->user['balance'] - $order->price),
            'user_id'=>$request->user['id'],
            'manager_id'=>$request->user['id'],
        ]);
        $payment->save();

        event(new PaymentWasCreated($payment));

        $order->payment_id = $payment->id;
        $order->save();

        $file = File::where([
            ['id', '=', $request->file_id],
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        if (!$request->user->hasRole('client')) {
            $orders = Order::select(['orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'orders.address', 'orders.comment', 'status_id', 'status.name as status_name', 'orders.price as price', 'orders.quantity as quantity', 'user_id', 'user.name as user_name', 'file.old_name as file_name', 'plate_id'])
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->join('users as user', 'user.id', '=', 'orders.user_id')
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
            $orders = Order::select(['orders.id as id', 'c', 'm', 'y', 'k', 'pantone', 'urgent', 'deliver', 'address', 'comment', 'status_id', 'status.name as status_name', 'orders.price as price','orders.quantity as quantity', 'file.old_name as file_name', 'user_id', 'plate_id'])
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->leftJoin('files as file', function($q) {
                $q->on('file.filable_id', '=', 'orders.id');
                $q->where('file.filable_type', '=', 'App\Order');
                $q->orderBy('file.name', 'asc');
                $q->groupBy('file.filable_id');
            })
            ->where('user_id', $request->user->id)
            ->orderBy('id', 'desc')
            ->paginate(25);
        }
        return response()->json([
            'status' => 'success',
            'orders' => $orders,
            'message' => 'Список Заказов выбран.',
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
            $order = Order::where([
                ['id', '=', $request->id],
                ['status_id', '=', 1],
                ['user_id', '=', $request->user->id],
            ])->get(['id', 'c', 'm', 'y', 'k', 'pantone', 'price', 'quantity', 'urgent', 'deliver', 'address', 'comment', 'status_id', 'user_id', 'plate_id'])->first();
        }
        else{
            $order = Order::where([
                ['id', '=', $request->id],
            ])->get(['id', 'c', 'm', 'y', 'k', 'pantone', 'price', 'quantity', 'urgent', 'deliver', 'address', 'comment', 'status_id', 'user_id', 'plate_id'])->first();
        }

        

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
            'file_id'=>'required|integer',
            'order.plateId'=>'required|integer'
        ]);

        $order = Order::find($request->order['id']);

        if ( $request->user->hasRole('client') && ($order->user_id != $request->user->id || (
            $order->user_id == $request->user->id && $order->status_id != 1))){
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя изменить'], 403);
        }
        $previousOrderPrice = $order->price;
        $order->plate_id = $request->order['plateId'];
        $order->update($request->order);
        $order->status_id = ($request->user->hasRole('client')) ? 1 : $request->status_id;

        $order->price = $this->calculateOrderPrice([
            'plate_id'=>$request->order['plateId'],
            'quantity'=>$request->order['quantity'],
            'c'=>$request->order['c'],
            'm'=>$request->order['m'],
            'y'=>$request->order['y'],
            'k'=>$request->order['k'],
            'pantone'=>$request->order['pantone'],
        ], $request->user);

        $payment = Payment::find($order->payment_id);
        $payment->amount = (-1) * $order->price;
        $payment->name = 'order update';
        $payment->balance_before = $request->user['balance'] + $previousOrderPrice;
        $payment->balance_after = ($request->user['balance'] + $previousOrderPrice) - $order->price;
        $payment->manager_id = $request->user['id'];

        $payment->save();
        
        //user balance correct calculation
        $payment->amount = $previousOrderPrice - $order->price;

        event(new PaymentWasCreated($payment));

        $order->save();

        return response()->json([
            'status' => 'success',
            'order' => $order,
            'payment' => $payment,
            'message' => 'Заказ изменен',
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

        $order = Order::where([
            ['id', '=', $request->id],
            ['status_id', '=', 1],
            ['user_id', '=', $request->user->id],
        ])->first();
        
        $file = File::where([
            ['filable_id', '=', $request->id],
            ['filable_type', '=', 'App\Order'],
        ])->first();

        if (empty($order)) {
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя удалить или его нет'], 403);
        }

        if (empty($file)) {
            return response()->json(['status' => 'error', 'message' => 'Файл не найден'], 400);
        }
 
        $file_path = public_path($this->uploadsPath) . '/' . $file->name;
 
        if (file_exists($file_path)) {
            unlink($file_path);
        }
 
        if (!empty($file)) {
            $file->delete();
        }

        if (!empty($order)) {
            $order->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Заказ Удален',
        ]);
    }

    private function calculateOrderPrice($args, $user) {
        $plate = Plate::find($args['plate_id']);
        $price = $plate->price;
        foreach ($user->pricing as $key => $platePrice) {
            if ($platePrice['plate_id'] == $args['plate_id']) {
                $price = $platePrice['price'];
            }
        }
        $selectedColors = count(array_filter([
            $args['c'],
            $args['m'],
            $args['y'],
            $args['k'],
            $args['pantone'],
        ]));
        return ($price * $args['quantity'] * $selectedColors);
    }
}

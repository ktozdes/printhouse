<?php

namespace App\Http\Controllers;

use App\Order;
use App\File;
use Illuminate\Http\Request;

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
            $orders = Order::orderBy('id', 'desc')->paginate(15);
        }
        else{
            $orders = Order::where('user_id', $request->user->id)->orderBy('id', 'desc')->paginate(15);
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
        $order = Order::where([
            ['id', '=', $request->id],
            ['status_id', '=', 1],
            ['user_id', '=', $request->user->id],
        ])->get(['id', 'c', 'm', 'y', 'k', 'urgent', 'deliver', 'address', 'comment', 'status_id', 'user_id', 'plate_id'])->first();

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
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя изменить'], 400);
        }
        $order->status_id = 1;
        $order->plate_id = $request->order['plateId'];
        $order->update($request->order);

        return response()->json([
            'status' => 'success',
            'request' => $request->order,
            'order' => $order,
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
            return response()->json(['status' => 'error', 'message' => 'Этот заказ нельзя удалить или его нет'], 400);
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
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Payment;
use App\Order;
use App\Storage;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function balance(Request $request)
    {
        $searchFilter = [];
        $sortBy = 'payments.created_at';
        $dir = 'desc';
        
        if ($request->user->hasRole('client')) {
            $searchFilter[] = ['user_id', '=', $request->user->id];
        }
        else if (isset($request->user_id) && is_numeric($request->user_id)) {
            $searchFilter[] = ['user_id', '=', $request->user_id];
        }
        if (isset( $request->start_time )) {
            $searchFilter[] = ['payments.created_at', '>=', $request->start_time];
        }
        if (isset( $request->end_time)) {
            $searchFilter[] = ['payments.created_at', '<=', $request->end_time . ' 23:59:59'];
        }
        if (isset( $request->balance_type) && $request->balance_type != 'all') {
            $sign = $request->balance_type == 'balance_positive' ? '>' : '<';
            $searchFilter[] = ['payments.amount', $sign , 0];
        }

        if($request->sort_by == 'date_asc') {
            $dir = 'asc';
        }
        else if( $request->sort_by == 'payment_desc') {
            $sortBy = 'payments.amount';
        }
        else if( $request->sort_by == 'payment_asc') {
            $sortBy = 'payments.amount';
            $dir = 'asc';
        }
        $payments = Payment::select(['payments.amount', 'payments.balance_before', 'payments.balance_after', 'payments.name as name', 'payments.created_at as created_at', 'payments.comment as comment', 'manager_id', 'user_id', 'payer.name as payer_name', 'manager.name as manager_name'])
            ->join('users as payer', 'payer.id', '=', 'payments.user_id')
            ->join('users as manager', 'manager.id', '=', 'payments.manager_id')
            ->where( $searchFilter )
            ->orderBy($sortBy, $dir)
            ->paginate(25);

        return response()->json([
            'status' => 'success',
            'ddd' => $searchFilter,
            'payments' => $payments,
        ]);
    }


    public function order(Request $request)
    {
        $searchFilter = [];
        $sortBy = 'orders.created_at';
        $dir = 'desc';
        
        if ($request->user->hasRole('client')) {
            $searchFilter[] = ['orders.user_id', '=', $request->user->id];
        }
        else if (isset($request->user_id) && is_numeric($request->user_id)) {
            $searchFilter[] = ['orders.user_id', '=', $request->user_id];
        }
        if (isset( $request->start_time )) {
            $searchFilter[] = ['orders.created_at', '>=', $request->start_time];
        }
        if (isset( $request->end_time)) {
            $searchFilter[] = ['orders.created_at', '<=', $request->end_time . ' 23:59:59'];
        }
        if (isset( $request->status_id ) && $request->status_id != 'all') {
            $searchFilter[] = ['orders.status_id', '=',  $request->status_id];
        }

        if($request->sort_by == 'date_asc') {
            $dir = 'asc';
        }
        else if( $request->sort_by == 'price_desc') {
            $sortBy = 'orders.price';
        }
        else if( $request->sort_by == 'price_desc') {
            $sortBy = 'orders.price';
            $dir = 'asc';
        }
        $orders = Order::select(['c', 'm', 'y', 'k', 'pantone', 'orders.user_id as user_id', 'orders.comment as comment', 'status_id', 'status.name as status_name',
            'payment.amount as price', 'storage.quantity as quantity', 'storage.plate_id', 
            'plate.name as plate_name', 'user.name as user_name', 'orders.created_at as created_at'])
            ->join('statuses as status', 'status.id', '=', 'orders.status_id')
            ->join('users as user', 'user.id', '=', 'orders.user_id')
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
            ->leftJoin('payments as payment', function($plateQuery) {
                $plateQuery->on('payment.id', '=', 'orders.payment_id');
            })
            ->where( $searchFilter )
            ->orderBy($sortBy, $dir)
            ->paginate(25);

        return response()->json([
            'status' => 'success',
            'orders' => $orders,
        ]);
    }


    public function storage(Request $request)
    {
        if ($request->user->hasRole('client')) {
            return response()->json(['status' => 'error', 'message' => 'У вас недостаточно прав'], 403);
        }

        $searchFilter = [];
        $sortBy = 'storages.created_at';
        $dir = 'desc';

        
        if (isset( $request->start_time )) {
            $searchFilter[] = ['storages.created_at', '>=', $request->start_time];
        }
        if (isset( $request->end_time)) {
            $searchFilter[] = ['storages.created_at', '<=', $request->end_time . ' 23:59:59'];
        }
        if (isset( $request->plate_id ) && $request->plate_id != 'all') {
            $searchFilter[] = ['storages.plate_id', '=',  $request->plate_id];
        }

        if($request->sort_by == 'date_asc') {
            $dir = 'asc';
        }
        else if( $request->sort_by == 'plate_name_desc') {
            $sortBy = 'plates.name';
        }
        else if( $request->sort_by == 'plate_name_asc') {
            $sortBy = 'plates.name';
            $dir = 'asc';
        }
        else if( $request->sort_by == 'quantity_desc') {
            $sortBy = 'storages.quantity';
        }
        else if( $request->sort_by == 'quantity_asc') {
            $sortBy = 'storages.quantity';
            $dir = 'asc';
        }
        else if( $request->sort_by == 'price_desc') {
            $sortBy = 'storages.price';
        }
        else if( $request->sort_by == 'price_asc') {
            $sortBy = 'storages.price';
            $dir = 'asc';
        }
        $storages = Storage::select(['storages.name', 'storages.quantity', 'storages.price', DB::raw('storages.quantity * storages.price as summa'), 'storages.plate_id', 'storages.manager_id', 'plates.name as plate_name', 'users.name as manager_name', 'storages.created_at as created_at'])
            ->join('users', 'users.id', '=', 'storages.manager_id')
            ->join('plates', 'plates.id', '=', 'storages.plate_id')
            ->where( $searchFilter )
            ->orderBy($sortBy, $dir)
            ->paginate(25);

        return response()->json([
            'status' => 'success',
            'storages' => $storages,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Payment;
use App\Order;
use App\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function chart_data(Request $request)
    {
        $searchFilter = [];
        $sortBy = 'payments.created_at';
        $dir = 'desc';
        
        

        $revenue = $this->revenue($request);
        $orderByUsers = $this->orderByUsers($request);
        $platesByPopularity = $this->platesByPopularity($request);
        $salesByMonth = $this->salesByMonth($request);
        $salesByManager = $this->salesByManager($request);

        return response()->json([
            'status' => 'success',
            'revenue' => $revenue,
            'order_by_user' => $orderByUsers,
            'plates_by_popularity' => $platesByPopularity,
            'sales_by_month' => $salesByMonth,
            'sales_by_manager' => $salesByManager,
        ]);
    }
    
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
            $searchFilter[] = ['payments.created_at', '>=', $request->start_time . ' 00:00:01'];
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



    public function revenue(Request $request)
    {
        $searchFilter = [];
        if (isset( $request->start_time )) {
            $searchFilter[] = ['payments.created_at', '>=', $request->start_time . ' 00:00:01'];
        }
        if (isset( $request->end_time)) {
            $searchFilter[] = ['payments.created_at', '<=', $request->end_time . ' 23:59:59'];
        }

        $payments = DB::table('payments')
            ->select(DB::raw("name, sum(amount) as value"))
            ->where( $searchFilter )
            ->groupBy('name')
            ->get();
        $totalExpense = 0;
        $totalPaid = 0;
        $labels = [
            'order' => [
                'name' => 'Заказ',
                'bgcolor' => 'deeppink'
            ],
            'defect' => [
                'name' => 'Брак',
                'bgcolor' => 'deeppink'
            ],
            'expense' => [
                'name' => 'Расход',
                'bgcolor' => 'deeppink'
            ],
            'payment' => [
                'name' => 'Оплата',
                'bgcolor' => 'limegreen'
            ],
            'income' => [
                'name' => 'Приход',
                'bgcolor' => 'limegreen'
            ],
            'profit' => [
                'name' => 'Прибыль',
                'bgcolor' => 'orange'
            ]
        ];

        $returnValue = [];
        $returnValue['label'] = [];
        if (count($payments) > 0 ) {
            foreach ($payments as $key => $singleRow) {
                if (!in_array($labels[$singleRow->name]['name'], $returnValue['label'])) {
                    $returnValue['label'][] = $labels[$singleRow->name]['name'];
                }
                $returnValue['value'][0]['data'][] = abs($singleRow->value);
                $returnValue['value'][0]['backgroundColor'][] = $labels[$singleRow->name]['bgcolor'];
                $returnValue['value'][0]['label'] = 'Значение';
                if ($singleRow->name == 'order' || $singleRow->name == 'defect') {
                    $totalExpense += abs($singleRow->value);
                }
                else if ($singleRow->name == 'payment'){
                    $totalPaid = abs($singleRow->value);
                }
            }
            $returnValue['value'][0]['data'][] = $totalExpense;
            $returnValue['value'][0]['backgroundColor'][] = $labels['expense']['bgcolor'];
            $returnValue['label'][] = $labels['expense']['name'];

            $returnValue['value'][0]['data'][] = $totalPaid - $totalExpense;
            $returnValue['value'][0]['backgroundColor'][] = $labels['profit']['bgcolor'];
            $returnValue['label'][] = $labels['profit']['name'];
        }
        return $returnValue;
    }

    public function orderByUsers(Request $request)
    {
        $searchFilter = [];
        $paidFilter = [];
        if (isset( $request->start_time )) {
            $searchFilter[] = ['orders.created_at', '>=', $request->start_time . ' 00:00:01'];
            $paidFilter[] = ['created_at', '>=', $request->start_time . ' 00:00:01'];
        }
        if (isset( $request->end_time)) {
            $searchFilter[] = ['orders.created_at', '<=', $request->end_time . ' 23:59:59'];
            $paidFilter[] = ['created_at', '<=', $request->end_time . ' 23:59:59'];
        }
        $paidFilter[] = ['name', '=', 'payment'];

        $result = DB::table('orders')
            ->select(DB::raw("orders.user_id, users.name, sum(ABS(payments.amount)) as amount, sum(quantity) as quantity, income_payment.paid"))
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('payments', 'orders.payment_id', '=', 'payments.id')
            ->leftJoinSub(
                DB::table('storages')
                ->select(DB::raw("order_id, SUM(quantity) as quantity"))
                ->groupBy('order_id'),
                'storage', function($join) {
                $join->on('storage.order_id', '=', 'orders.id');
            })
            ->leftJoinSub(
                DB::table('payments')
                ->select(DB::raw("user_id as user_id, SUM(amount) as paid"))
                ->where($paidFilter)
                ->groupBy('user_id'),
                'income_payment', function($join) {
                $join->on('income_payment.user_id', '=', 'orders.user_id');
            })
            ->where( $searchFilter )
            ->groupBy('orders.user_id')
            ->orderBy('amount', 'asc')
            ->get();
        $labels = [
            'Заказано',
            'Оплачено',
            'Прибыль',
        ];

        $returnValue = [];
        $returnValue['label'] = [];
        if (count($result) > 0 ) {
            foreach ($result as $key => $singleRow) {
                if (!in_array($singleRow->name, $returnValue['label'])) {
                    $returnValue['label'][] = $singleRow->name;
                }
                foreach ($labels as $key => $singleLabel) {
                    $value = $singleRow->amount;
                    if ($singleLabel == 'Оплачено') {
                        $value = $singleRow->paid;
                    }
                    else if ($singleLabel == 'Прибыль'){
                        $value = $singleRow->paid - $singleRow->amount;
                    }
                    $returnValue['value'][$key]['data'][] = $value;
                    $returnValue['value'][$key]['label'] = $singleLabel;
                }
            }
        }
        return $returnValue;

    }

    public function platesByPopularity(Request $request)
    {
        $searchFilter = [];
        if (isset( $request->start_time )) {
            $searchFilter[] = ['storages.created_at', '>=', $request->start_time . ' 00:00:01'];
        }
        if (isset( $request->end_time)) {
            $searchFilter[] = ['storages.created_at', '<=', $request->end_time . ' 23:59:59'];
        }

        $plates = DB::table('storages')
            ->select(DB::raw("storages.name as storage_name, sum(storages.quantity) as quantity, plates.name as plate_name"))
            ->join('plates', 'storages.plate_id', '=', 'plates.id')
            ->where( $searchFilter )
            ->where(function($q) {
                $q->where('storages.name', '=', 'order')
                ->orWhere('storages.name', '=', 'defect');
            })
            ->groupBy('storage_name', 'plate_name')
            ->orderBy('plate_name', 'asc')
            ->get();
        $returnValue = [];
        $returnValue['label'] = [];
        foreach ($plates as $key => $singleRow) {
            if (!in_array($singleRow->plate_name, $returnValue['label'])) {
                $returnValue['label'][] = $singleRow->plate_name;
            }


            if (!isset($returnValue['value'][$singleRow->plate_name])) {
                $returnValue['value'][$singleRow->plate_name]['value'] = $singleRow->quantity;
            }
            else {
                $returnValue['value'][$singleRow->plate_name]['value'] += $singleRow->quantity;
            }
            $returnValue['value'][$singleRow->plate_name]['type'][$singleRow->storage_name] = $singleRow->quantity;
        }
        $returnValue['value'] = array_values($returnValue['value']);
        return $returnValue;
    }

    public function salesByMonth(Request $request)
    {
        $twoYearsAgo = Carbon::now()->subYears(2);
        $payments = DB::table('payments')
            ->select(DB::raw("SUM(amount) as value, concat_ws(' ', YEAR(created_at), MONTHNAME(created_at)) as name"))
            ->where( 'payments.created_at', '>=', $twoYearsAgo->format('Y/m/d') . ' 00:00:01')
            ->groupBy( DB::raw("concat_ws(' ', YEAR(created_at), MONTHNAME(created_at))") )
            ->orderBy( DB::raw("concat_ws(' ', YEAR(created_at), MONTHNAME(created_at))") )
            ->get();
        $currentTotal = 0;
        $returnValue = [];
        $returnValue['value'] = [];
        if (count($payments) > 0 ) {
            foreach ($payments as $key => $singleRow) {
                $currentTotal += $singleRow->value;
                $returnValue['label'][] = $singleRow->name;
                $returnValue['value'][0]['data'][] = $singleRow->value;
                $returnValue['value'][0]['label'] = 'Прибыль';

                $returnValue['value'][1]['data'][] = round($currentTotal / ($key+1), 2);
                $returnValue['value'][1]['label'] = 'Среднее';

                //data: [65, 59, 80, 81, 56, 55, 40], label: 'Series A'
            }
            $returnValue['value'][2]['data'] = array_fill(0, count($payments), round($currentTotal / count($payments), 2));
            $returnValue['value'][2]['label'] = 'Общее Среднее';
        }
        return $returnValue;
    }


    public function salesByManager(Request $request)
    {
        $twoYearsAgo = Carbon::now()->subYears(2);
        $managersSales = DB::table('storages')
            ->select(DB::raw("sum(storages.quantity) as quantity, users.name as manager_name, sum( abs( payments.amount ) ) as amount,concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)) as date_name"))
            ->join('users', 'users.id', '=', 'storages.manager_id')
            ->leftJoin('orders', 'orders.id', '=', 'storages.order_id')
            ->leftJoin('payments', 'payments.id', '=', 'orders.payment_id')
            ->where([
                ['orders.status_id', 3],
                ['storages.updated_at', '>=', $twoYearsAgo->format('Y/m/d') . ' 00:00:01']
            ] )
            ->groupBy( DB::raw("concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)), users.name") )
            ->orderBy( DB::raw("concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)), users.name") )
            ->get();
        $managersDefect = DB::table('storages')
            ->select(DB::raw("sum(quantity) as quantity, sum(quantity * price) as amount, users.name as manager_name, concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)) as date_name"))
            ->join('users', 'users.id', '=', 'storages.manager_id')
            ->where([
                ['storages.name', 'defect'],
                ['storages.updated_at', '>=', $twoYearsAgo->format('Y/m/d') . ' 00:00:01']
            ] )
            ->groupBy( DB::raw("concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)), users.name") )
            ->orderBy( DB::raw("concat_ws(' ', YEAR(storages.updated_at), MONTHNAME(storages.updated_at)), users.name") )
            ->get();

        $returnValue = [];
        $returnValue['value'] = [];
        if (count($managersSales) > 0 ) {
            foreach ($managersSales as $key => $sale) {
                $returnValue['label'][] = $sale->date_name;
                $returnValue['value'][$sale->manager_name][0]['data'][] = $sale->amount;
                $returnValue['value'][$sale->manager_name][0]['label'] = 'Заказано';
                $returnValue['value'][$sale->manager_name][1]['data'][] = $sale->quantity;
                $returnValue['value'][$sale->manager_name][1]['label'] = 'Продано Пластин';

                $defect = $this->findValueInArray($managersDefect, $sale->manager_name, $sale->date_name);
                if ($defect) {
                    $returnValue['value'][$sale->manager_name][2]['data'][] = $defect->amount;
                    $returnValue['value'][$sale->manager_name][2]['label'] = 'Цена Брака';
                    $returnValue['value'][$sale->manager_name][3]['data'][] = $defect->quantity;
                    $returnValue['value'][$sale->manager_name][3]['label'] = 'Кол. Брак Пласт';
                }
                //data: [65, 59, 80, 81, 56, 55, 40], label: 'Series A'
            }
            //$returnValue['value'] = array_values($returnValue['value']);
        }
        return $returnValue;
    }

    private function findValueInArray($payload, $name, $date)
    {
        foreach ($payload as $key => $value) {
            if ($value->manager_name == $name && $value->date_name == $date){
                return $value;
            }
        }
        return false;
    }
}

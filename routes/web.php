<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//use Illuminate\Support\Facades\Storage;
use App\Order;
//use App\Storage;

use App\Events\PaymentWasCreated;
use App\Events\PlateQuantityChanged;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {


    $inputStorages = 
    DB::table('storages as inp')
    ->select(DB::raw("inp.plate_id, inp.id, inp.quantity, inp.updated_at, min_value"))
    ->leftJoinSub(
        DB::table('storages')
	    ->select(DB::raw("used_storage_id, min(quantity_after) as min_value"))
	    ->groupBy('used_storage_id'), 
	    'outp', function($join) {
        $join->on('inp.id', '=', 'outp.used_storage_id');
    })
    ->where([
    	['inp.name', 'storage'],
        ['plate_id', 1],
    ])
    ->where(function($q) {
		$q->where('min_value', '>=', 0)
        ->orWhereNull('min_value');
  	})
    ->orderBy('inp.updated_at', 'desc')
    ->get();

    print_r($inputStorages);

	try {
	    DB::connection()->getPdo();
	} catch (\Exception $e) {
	    die("Could not connect to the database.  Please check your configuration. error:" . $e );
	}
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

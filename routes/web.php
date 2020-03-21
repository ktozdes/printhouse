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
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
	echo url('/')."<br/>";
	echo Storage::disk('public_path')->url('hello/');
	try {
	    DB::connection()->getPdo();
	} catch (\Exception $e) {
	    die("Could not connect to the database.  Please check your configuration. error:" . $e );
	}
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

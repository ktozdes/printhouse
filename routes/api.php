<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('/login', 'Auth\LoginController@login')->middleware(['cors']);
Route::post('/signback', 'Auth\LoginController@signback')->middleware(['cors']);
Route::post('/register', 'Auth\LoginController@register')->middleware(['cors']);

Route::get('/show', 'UserController@show')->middleware(['cors', 'token']);
Route::get('/user/edit', 'UserController@edit')->middleware(['cors', 'token']);
Route::post('/user/update', 'UserController@update')->middleware(['cors', 'token']);
Route::post('/user/store', 'UserController@store')->middleware(['cors', 'token', 'permission:menu user']);
Route::get('/user/list', 'UserController@list')->middleware(['cors', 'token', 'permission:menu user']);
Route::get('/user/list_manager', 'UserController@listManager')->middleware(['cors', 'token', 'permission:report manager']);

Route::get('/plate/list', 'PlateController@list')->middleware(['cors', 'token']);
Route::get('/plate/edit', 'PlateController@edit')->middleware(['cors', 'token', 'role:super-admin']);
Route::post('/plate/update', 'PlateController@update')->middleware(['cors', 'token', 'role:super-admin']);
Route::post('/plate/store', 'PlateController@store')->middleware(['cors', 'token', 'role:super-admin']);
Route::post('/plate/destroy', 'PlateController@destroy')->middleware(['cors', 'token', 'role:super-admin']);

Route::post('/storage/add_defect', 'StorageController@addDefect')->middleware(['cors', 'token', 'permission:menu defect']);


Route::post('/payment/store', 'PaymentController@store')->middleware(['cors', 'token', 'permission:menu payment']);

Route::post('/file/upload', 'FileController@upload')->middleware(['cors', 'token']);
Route::post('/file/destroy', 'FileController@destroy')->middleware(['cors', 'token']);

Route::get('/order/list', 'OrderController@list')->middleware(['cors', 'token', 'response']);
Route::post('/order/store', 'OrderController@store')->middleware(['cors', 'token']);
Route::get('/order/edit', 'OrderController@edit')->middleware(['cors', 'token']);
Route::post('/order/update', 'OrderController@update')->middleware(['cors', 'token']);
Route::post('/order/change_status', 'OrderController@changeStatus')->middleware(['cors', 'token', 'permission:order user all']);
Route::post('/order/destroy', 'OrderController@destroy')->middleware(['cors', 'token']);

Route::get('/report/chart_data', 'ReportController@chart_data')->middleware(['cors', 'token', 'permission:report chart']);
Route::get('/report/manager', 'ReportController@manager')->middleware(['cors', 'token', 'permission:report manager']);
Route::get('/report/balance', 'ReportController@balance')->middleware(['cors', 'token']);
Route::get('/report/order', 'ReportController@order')->middleware(['cors', 'token']);
Route::get('/report/storage', 'ReportController@storage')->middleware(['cors', 'token']);

Route::get('/status/list', 'StatusController@list')->middleware(['cors', 'token']);


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


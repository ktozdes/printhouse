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

Route::get('/plate/list', 'PlateController@list')->middleware(['cors', 'token']);
Route::post('/plate/store', 'PlateController@store')->middleware(['cors', 'token']);


Route::post('/file/upload', 'FileController@upload')->middleware(['cors', 'token']);
Route::get('/file/destroy', 'FileController@destroy')->middleware(['cors', 'token']);

Route::get('/order/list', 'OrderController@list')->middleware(['cors', 'token']);
Route::post('/order/store', 'OrderController@store')->middleware(['cors', 'token']);
Route::get('/order/edit', 'OrderController@edit')->middleware(['cors', 'token']);
Route::post('/order/update', 'OrderController@update')->middleware(['cors', 'token']);
Route::post('/order/destroy', 'OrderController@destroy')->middleware(['cors', 'token']);


// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


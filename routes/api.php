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
Route::post('/paycomplete', 'CustomersController@verify');
Route::post('/customers/checkout/verify', 'CustomersController@verify');
Route::middleware('auth:api')->get('/user', function (Request $request) {
    Route::post('/paycomplete', 'CustomersController@verify');
    // return $request->user();
});

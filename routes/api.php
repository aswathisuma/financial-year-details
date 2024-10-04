<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', 'AuthController@register')->middleware(['log.api']);
Route::post('/login', 'AuthController@login')->middleware(['log.api', 'throttle:10,1']);
Route::middleware(['auth:sanctum', 'log.api'])->group(function ()  {
    Route::get('user/blogs', 'AuthController@blogs');
    Route::post('user/blogs/create', 'AuthController@create');
    Route::post('user/blogs/update', 'AuthController@update');
    Route::post('user/blogs/delete', 'AuthController@delete');
});

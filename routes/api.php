<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'Api\AuthController@register');
Route::post('/login', 'Api\AuthController@login');

Route::post('/main_heading_create', 'Api\Main_HeadingController@store')->middleware('auth:api');
Route::patch('/main_heading_update', 'Api\Main_HeadingController@update')->middleware('auth:api');
Route::delete('/main_heading_delete', 'Api\Main_HeadingController@destroy')->middleware('auth:api');
Route::get('/main_heading_show', 'Api\Main_HeadingController@show')->middleware('auth:api');
Route::get('/main_heading_list', 'Api\Main_HeadingController@index')->middleware('auth:api');

Route::post('/calendar_create', 'Api\CalendarController@store')->middleware('auth:api');
Route::get('/calendar_list', 'Api\CalendarController@index')->middleware('auth:api');
Route::get('/calendar_show', 'Api\CalendarController@show')->middleware('auth:api');

<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/prepareCompare', "MainController@prepareCompareView");
Route::get('/startCompare', "MainController@startCompare");
Route::get('/showCompare', "MainController@showOsQueueProcess");
Route::get('/allegro', "MainController@allegro");
Route::get('/getToken', "MainController@getAccesToken");
Route::get('/checkUser', "MainController@checkUserAcceptance");
Route::get('/refreshTokens', "MainController@refreshTokens");
Route::get('/compare/{marketplace}', "MainController@compareMarketplace");
Route::get('/stop-queue', "MainController@stopOsQueueProcess");
Route::get('/test', "MainController@test");

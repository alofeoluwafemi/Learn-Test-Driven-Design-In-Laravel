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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/post/create','PostController@create');
Route::post('/post/create','PostController@store')->middleware('auth');
Route::delete('/post/delete/{blog}','PostController@destroy')->middleware('auth');
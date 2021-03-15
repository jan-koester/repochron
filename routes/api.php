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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

// RepoKron Actions
Route::namespace('App\Http\Controllers\repochron') -> group(function() {

    // Show all available versions of specific file
    Route::get('/{directory}/{file}::log', 'MainController@showLog') -> where('directory', '.+');

    // Show specific version of file identified by Revision or Date
    Route::get('/{directory}/{file}::{id}', 'MainController@showVersion') -> where('directory', '.+');
});

// No RepoKron Actions required, redirect to storage
Route::get('/{directory}/{file}', function($directory, $file) { return redirect('/storage/'.$directory.'/'.$file); }) -> where('directory', '.+');

<?php

use App\Http\Middleware\CheckAccess;
use CloudCreativity\LaravelJsonApi\Facades\JsonApi;
use CloudCreativity\LaravelJsonApi\Routing\RouteRegistrar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');

Route::middleware('auth:sanctum')
    ->get('user', function (Request $request) {
        $user = $request->user();
        return new JsonResponse([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'is_admin' => $user->is_admin,
        ], 200);
    });

JsonApi::register('default')
    ->middleware('auth:sanctum')
    ->routes(function (RouteRegistrar $api) {
        $api->resource('users')->relationships(function ($relations) {
            $relations->hasMany('timesheets');
        });
        $api->resource('timesheets')->relationships(function ($relations) {
            $relations->hasMany('shifts');
            $relations->hasOne('user');
        });
        $api->resource('shifts')->relationships(function ($relations) {
            $relations->hasOne('timesheet');
        });
    });

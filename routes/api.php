<?php

use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Products\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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

Route::group([
    'prefix'    =>      'auth'
], function (){
    Route::post('login', [AuthController::class, 'Login']);
    Route::post('signup', [AuthController::class, 'SignUp']);
    Route::get('signup/activate/{token}', [AuthController::class, 'SignupActivate']);

    Route::group([
        'middleware'        =>      'auth:api'
    ], function () {
        Route::get('logout', [AuthController::class, 'Logout']);
        Route::get('user', [AuthController::class, 'User']);

        Route::get('products', [ProductsController::class, 'Index']);
        Route::post('products/create', [ProductsController::class, 'Create']);
        Route::post('products/{id}', [ProductsController::class, 'Update']);
        Route::delete('products/delete/{id}', [ProductsController::class, 'Delete']);
    });
});

Route::group([
    'namespace' => 'Auth',
    'middleware' => 'api',
    'prefix' => 'password'
], function () {
    Route::post('create', [PasswordResetController::class, 'Create']);
    Route::get('find/{token}', [PasswordResetController::class, 'Find']);
    Route::post('reset', [PasswordResetController::class, 'Reset']);
});




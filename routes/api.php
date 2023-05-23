<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillboardController;
use App\Http\Controllers\CinemaController;
use App\Http\Controllers\PrintUnitController;
use App\Http\Controllers\RadioUnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierOwnerController;
use App\Http\Controllers\TVUnitController;
use App\Http\Controllers\UnitOrderController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('update-profile', 'updateProfile');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');

    Route::post('reset-pass-link', 'sendResetLinkEmail');
    Route::post('reset-pass', 'resetPassword');

});

Route::controller(SupplierController::class)->group(function () {
    Route::get('suppliers', 'getAllSuppliers');
    Route::get('suppliers/{id}', 'getSupplier');

});

Route::controller(SupplierOwnerController::class)->group(function () {
    Route::get('owners', 'getAllOwners');
    Route::get('owners/{id}', 'getOwner');

});

Route::controller(BillboardController::class)->group(function () {
    Route::get('billboards', 'getAllBillboards');
    Route::get('billboards/{id}', 'getBillboard');

});

Route::controller(CinemaController::class)->group(function () {
    Route::get('cinemas', 'getAllCinemas');
    Route::get('cinemas/{id}', 'getCinema');

});

Route::controller(RadioUnitController::class)->group(function () {
    Route::get('radios', 'getAllRadios');
    Route::get('radios/{id}', 'getRadio');

});

Route::controller(PrintUnitController::class)->group(function () {
    Route::get('prints', 'getAllPrints');
    Route::get('prints/{id}', 'getPrint');

});

Route::controller(TVUnitController::class)->group(function () {
    Route::get('tvs', 'getAllTVs');
    Route::get('tvs/{id}', 'getTV');

});

Route::controller(UnitOrderController::class)->group(function () {
    Route::post('create-order', 'createOrder');
    Route::get('get-my-orders', 'getAllMyOrders');
    Route::delete('delete-order/{order_id}', 'deleteOrder');
    Route::post('pay-order/{order_id}', 'payOrder');
});

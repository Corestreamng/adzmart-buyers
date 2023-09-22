<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillboardController;
use App\Http\Controllers\CinemaController;
use App\Http\Controllers\ExpertReqController;
use App\Http\Controllers\PrintUnitController;
use App\Http\Controllers\RadioUnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierOwnerController;
use App\Http\Controllers\TVUnitController;
use App\Http\Controllers\UnitOrderController;
use App\Http\Controllers\MailingListController;

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
    Route::post('update-profile-pic', 'updateProfilePic');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');

    Route::post('reset-pass-link', 'sendResetLinkEmail');
    Route::post('reset-pass', 'resetPassword');
});

Route::controller(AdminAuthController::class)->group(function () {
    Route::post('admin/login', 'login');
    Route::post('admin/register', 'register');
    Route::post('admin/update-profile', 'updateProfile');
    Route::post('admin/logout', 'logout');
    Route::post('admin/refresh', 'refresh');

    Route::post('admin/reset-pass-link', 'sendResetLinkEmail');
    Route::post('admin/reset-pass', 'resetPassword');
    Route::get('get-user-profile', 'getUserProfile');
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

Route::controller(AdminController::class)->group(function () {
    Route::get('get-all-orders', 'getAllOrders');
    Route::get('get-all-users', 'getAllUsers');
    Route::get('get-all-order-stats', 'getAllOrdersByStatus');
    Route::get('get-all-radio-order-items', 'getAllRadioOrderItems');
    Route::get('get-running-radio-order-items', 'getRunningRadioOrderItems');
    Route::get('get-complete-radio-order-items', 'getCompleteRadioOrderItems');
    Route::get('get-pending-radio-order-items', 'getPendingRadioOrderItems');
    Route::get('get-declined-radio-order-items', 'getDeclinedRadioOrderItems');

    Route::get('get-all-tv-order-items', 'getAllTVOrderItems');
    Route::get('get-all-tv-order-items', 'getAllTVOrderItems');
    Route::get('get-running-tv-order-items', 'getRunningTVOrderItems');
    Route::get('get-complete-tv-order-items', 'getCompleteTVOrderItems');
    Route::get('get-pending-tv-order-items', 'getPendingTVOrderItems');
    Route::get('get-declined-tv-order-items', 'getDeclinedTVOrderItems');

    Route::get('get-all-cinema-order-items', 'getAllCinemaOrderItems');
    Route::get('get-all-cinema-order-items', 'getAllCinemaOrderItems');
    Route::get('get-all-cinema-order-items', 'getAllCinemaOrderItems');
    Route::get('get-running-cinema-order-items', 'getRunningCinemaOrderItems');
    Route::get('get-complete-cinema-order-items', 'getCompleteCinemaOrderItems');
    Route::get('get-pending-cinema-order-items', 'getPendingCinemaOrderItems');
    Route::get('get-declined-cinema-order-items', 'getDeclinedCinemaOrderItems');

    Route::get('get-all-billboard-order-items', 'getAllBillboardOrderItems');
    Route::get('get-all-billboard-order-items', 'getAllBillboardOrderItems');
    Route::get('get-all-billboard-order-items', 'getAllBillboardOrderItems');
    Route::get('get-running-billboard-order-items', 'getRunningBillboardOrderItems');
    Route::get('get-complete-billboard-order-items', 'getCompleteBillboardOrderItems');
    Route::get('get-pending-billboard-order-items', 'getPendingBillboardOrderItems');
    Route::get('get-declined-billboard-order-items', 'getDeclinedBillboardOrderItems');

    Route::get('get-all-print-order-items', 'getAllPrintOrderItems');
    Route::get('get-all-print-order-items', 'getAllPrintOrderItems');
    Route::get('get-all-print-order-items', 'getAllPrintOrderItems');
    Route::get('get-running-print-order-items', 'getRunningPrintOrderItems');
    Route::get('get-complete-print-order-items', 'getCompletePrintOrderItems');
    Route::get('get-pending-print-order-items', 'getPendingPrintOrderItems');
    Route::get('get-declined-print-order-items', 'getDeclinedPrintOrderItems');

    Route::get('get-all-funds', 'getAllFunds');
    Route::get('get-unverified-sellers', 'getAllUnverifiedSellers');
    Route::get('get-all-sellers', 'getAllSellers');
    Route::get('get-seller/{supplier_id}', 'getSeller');
    Route::post('approve-seller/{supplier_id}', 'approveSeller');
    Route::post('block-seller/{supplier_id}', 'blockSeller');
    Route::post('unblock-seller/{supplier_id}', 'unblockSeller');
    Route::post('block-user/{user_id}', 'blockUser');
    Route::post('unblock-user/{user_id}', 'unblockUser');
    Route::delete('delete-order/{order_id}', 'deleteOrder');
});

Route::controller(ExpertReqController::class)->group(function () {
    Route::get('get-all-expert-requests', 'index');
    Route::get('get-expert-request/{id}', 'show');
    Route::post('create-expert-request', 'store');
    Route::post('update-expert-request/{id}', 'update');
});

Route::controller(MailingListController::class)->group(function () {
    Route::post('subscribe', 'subscribe');
    Route::post('unsubscribe', 'unsubscribe');
});

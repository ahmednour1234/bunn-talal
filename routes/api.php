<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\BookingRequestController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DelegateLoanController;
use App\Http\Controllers\Api\HrLeaveApiController;
use App\Http\Controllers\Api\HrAttendanceApiController;
use App\Http\Controllers\Api\HrSalaryApiController;
use App\Http\Controllers\Api\DispatchController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SaleOrderController;
use App\Http\Controllers\Api\SaleReturnController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Delegate API Routes
|--------------------------------------------------------------------------
*/

// ── Public — no auth required ──────────────────────────────────────────
Route::post('/delegate/login', [AuthController::class, 'login']);

// ── Protected — requires valid Sanctum token + active delegate ──────────
Route::middleware(['auth:sanctum', 'delegate.active'])->group(function () {

    // Auth
    Route::post('/delegate/logout',          [AuthController::class, 'logout']);
    Route::post('/delegate/update-location', [AuthController::class, 'updateLocation']);

    // Statistics
    Route::get('/delegate/statistics',    [StatisticsController::class, 'index']);
    Route::get('/delegate/statistics/hr', [StatisticsController::class, 'hrStatistics']);

    // Profile
    Route::get('/delegate/profile', [ProfileController::class, 'show']);

    // Reference / Lookup Data
    Route::get('/delegate/units',           [UnitController::class, 'index']);
    Route::get('/delegate/accounts',        [AccountController::class, 'index']);
    Route::get('/delegate/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('/delegate/areas',           [AreaController::class, 'index']);
    Route::get('/delegate/categories',      [CategoryController::class, 'index']);
    Route::get('/delegate/categories/{category}/products', [ProductController::class, 'index']);
    Route::get('/delegate/trip/products', [ProductController::class, 'tripProducts']);
    Route::get('/delegate/customers',       [CustomerController::class, 'index']);
    Route::post('/delegate/customers',      [CustomerController::class, 'store']);

    // Loans / Custody
    Route::get('/delegate/loans', [DelegateLoanController::class, 'index']);

    // ── Trips ────────────────────────────────────────────────────────────
    Route::get('/delegate/trips',                [TripController::class, 'index']);
    Route::post('/delegate/trips',               [TripController::class, 'store']);
    Route::get('/delegate/trips/products',       [ProductController::class, 'tripProducts']); // must be before {trip}
    Route::get('/delegate/trips/{trip}',         [TripController::class, 'show']);
    Route::patch('/delegate/trips/{trip}/start', [TripController::class, 'start']);
    Route::patch('/delegate/trips/{trip}/end',   [TripController::class, 'end']);

    // ── Booking Requests (within a trip) ─────────────────────────────────
    Route::get('/delegate/trips/{trip}/booking-requests',  [BookingRequestController::class, 'index']);
    Route::post('/delegate/trips/{trip}/booking-requests', [BookingRequestController::class, 'store']);
    Route::get('/delegate/booking-requests/{bookingRequest}',          [BookingRequestController::class, 'show']);
    Route::patch('/delegate/booking-requests/{bookingRequest}/cancel', [BookingRequestController::class, 'cancel']);

    // ── Inventory Dispatches — View Only ─────────────────────────────────
    Route::get('/delegate/trips/{trip}/dispatches', [DispatchController::class, 'index']);
    Route::get('/delegate/dispatches/{dispatch}',   [DispatchController::class, 'show']);

    // ── Sale Orders ───────────────────────────────────────────────────────
    Route::get('/delegate/trips/{trip}/orders',  [SaleOrderController::class, 'index']);
    Route::post('/delegate/trips/{trip}/orders', [SaleOrderController::class, 'store']);
    Route::get('/delegate/orders/{order}',                    [SaleOrderController::class, 'show']);
    Route::post('/delegate/orders/{order}/payments',          [SaleOrderController::class, 'addPayment']);
    Route::patch('/delegate/orders/{order}/cancel',           [SaleOrderController::class, 'cancel']);

    // ── Collections (تحصيلات) ────────────────────────────────────────────
    Route::get('/delegate/collections',                   [CollectionController::class, 'myCollections']);
    Route::get('/delegate/trips/{trip}/collections',  [CollectionController::class, 'index']);
    Route::post('/delegate/trips/{trip}/collections', [CollectionController::class, 'store']);
    Route::get('/delegate/collections/{collection}',  [CollectionController::class, 'show']);

    // ── Sale Returns ──────────────────────────────────────────────────────
    Route::get('/delegate/trips/{trip}/returns',  [SaleReturnController::class, 'index']);
    Route::post('/delegate/trips/{trip}/returns', [SaleReturnController::class, 'store']);
    Route::get('/delegate/returns/{return}',      [SaleReturnController::class, 'show']);

    // ── HR - Leaves ───────────────────────────────────────────────────────
    Route::get('/delegate/hr/leaves',            [HrLeaveApiController::class, 'index']);
    Route::post('/delegate/hr/leaves',           [HrLeaveApiController::class, 'store']);
    Route::get('/delegate/hr/leaves/{leave}',    [HrLeaveApiController::class, 'show']);

    // ── HR - Attendance ───────────────────────────────────────────────────
    Route::get('/delegate/hr/attendance',               [HrAttendanceApiController::class, 'index']);
    Route::get('/delegate/hr/attendance/summary',       [HrAttendanceApiController::class, 'summary']);
    Route::get('/delegate/hr/attendance/{attendance}',  [HrAttendanceApiController::class, 'show']);

    // ── HR - Salaries ─────────────────────────────────────────────────────
    Route::get('/delegate/hr/salaries',          [HrSalaryApiController::class, 'index']);
    Route::get('/delegate/hr/salaries/{salary}', [HrSalaryApiController::class, 'show']);
});


<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomTypeController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\HousekeepingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HallController;
use App\Http\Controllers\Api\HallBookingController;
use App\Http\Controllers\Api\BreakfastController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\RestaurantOrderController;
use App\Http\Controllers\Api\LaundryOrderController;
use App\Http\Controllers\Api\MLController;
use App\Http\Controllers\Api\SettingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/public/bookings', [BookingController::class, 'publicStore']);
Route::post('/public/bookings/upload-receipt', [BookingController::class, 'uploadReceipt']);
Route::get('/public/bookings/search', [BookingController::class, 'publicSearch']);
Route::get('/public/room-types', [RoomTypeController::class, 'index']);
Route::get('/public/halls', [HallController::class, 'publicIndex']);
Route::post('/public/hall-bookings', [HallBookingController::class, 'publicStore']);
Route::get('/public/settings/payment', [SettingController::class, 'getPaymentSettings']);
Route::get('/public/settings/social', [SettingController::class, 'getSocialSettings']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/dashboard/refresh', [DashboardController::class, 'refresh']);

    // Room Types Management
    Route::apiResource('room-types', RoomTypeController::class);
    
    // Rooms Management
    Route::get('/rooms-statistics', [RoomController::class, 'statistics']);
    Route::get('/rooms/export', [RoomController::class, 'export']);
    Route::apiResource('rooms', RoomController::class);
    Route::patch('/rooms/{room}/status', [RoomController::class, 'updateStatus']);

    // Guests Management
    Route::get('/guests/export', [GuestController::class, 'export']);
    Route::apiResource('guests', GuestController::class);
    Route::get('/guests/search/{query}', [GuestController::class, 'search']);

    // Bookings Management
    Route::get('/bookings/check-availability', [BookingController::class, 'checkAvailability']);
    Route::get('/bookings/export', [BookingController::class, 'export']);
    Route::apiResource('bookings', BookingController::class);
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
    Route::post('/bookings/{booking}/check-in', [BookingController::class, 'checkIn']);
    Route::post('/bookings/{booking}/check-out', [BookingController::class, 'checkOut']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    // Payments Management
    Route::get('/payments/export', [PaymentController::class, 'export']);
    Route::apiResource('payments', PaymentController::class);
    Route::get('/bookings/{booking}/payments', [PaymentController::class, 'bookingPayments']);

    // System & Payment Settings
    Route::get('/settings/payment', [SettingController::class, 'getPaymentSettings']);
    Route::post('/settings/payment', [SettingController::class, 'updatePaymentSettings']);
    Route::get('/settings/social', [SettingController::class, 'getSocialSettings']);
    Route::post('/settings/social', [SettingController::class, 'updateSocialSettings']);

    // Housekeeping Management
    Route::get('/housekeeping-statistics', [HousekeepingController::class, 'statistics']);
    Route::apiResource('housekeeping', HousekeepingController::class);
    Route::patch('/housekeeping/{housekeeping}/status', [HousekeepingController::class, 'updateStatus']);

    // Breakfast Management
    Route::get('/breakfasts', [BreakfastController::class, 'index']);
    Route::get('/breakfasts/statistics', [BreakfastController::class, 'statistics']);
    Route::patch('/bookings/{booking}/breakfast', [BreakfastController::class, 'updateStatus']);

    // Users Management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);

    // Halls Management
    Route::get('/halls/types', [HallController::class, 'getTypes']);
    Route::get('/halls/{hall}/availability', [HallController::class, 'checkAvailability']);
    Route::apiResource('halls', HallController::class);

    // Hall Bookings Management
    Route::get('/hall-bookings/calendar', [HallBookingController::class, 'calendar']);
    Route::post('/hall-bookings/{hallBooking}/confirm', [HallBookingController::class, 'confirm']);
    Route::post('/hall-bookings/{hallBooking}/check-in', [HallBookingController::class, 'checkIn']);
    Route::post('/hall-bookings/{hallBooking}/cancel', [HallBookingController::class, 'cancel']);
    Route::post('/hall-bookings/{hallBooking}/complete', [HallBookingController::class, 'complete']);
    Route::apiResource('hall-bookings', HallBookingController::class);

    // Restaurant Management
    Route::apiResource('menu-items', MenuItemController::class);
    Route::get('/bookings/{booking}/restaurant-charges', [RestaurantOrderController::class, 'getBookingCharges']);
    Route::get('/restaurant-orders/export', [RestaurantOrderController::class, 'export']);
    Route::patch('/restaurant-orders/{restaurantOrder}/status', [RestaurantOrderController::class, 'updateStatus']);
    Route::apiResource('restaurant-orders', RestaurantOrderController::class);

    // Laundry Management
    Route::get('/bookings/{booking}/laundry-charges', [LaundryOrderController::class, 'getBookingCharges']);
    Route::get('/laundry-orders/export', [LaundryOrderController::class, 'export']);
    Route::patch('/laundry-orders/{laundryOrder}/status', [LaundryOrderController::class, 'updateStatus']);
    Route::apiResource('laundry-orders', LaundryOrderController::class)->only(['index', 'store', 'show', 'destroy']);

    // ML/AI Predictions
    Route::prefix('ml')->group(function () {
        // Owner only - training and predictions
        Route::post('/train', [MLController::class, 'trainModels'])->middleware('throttle:10,60'); // 10 per hour
        Route::post('/predict', [MLController::class, 'generatePredictions'])->middleware('throttle:20,60');
        
        // All authenticated users - view predictions
        Route::get('/predictions', [MLController::class, 'getPredictions']);
        Route::get('/info', [MLController::class, 'getModelInfo']);
    });
});

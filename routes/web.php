<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExchangeRateAudController;
use App\Http\Controllers\ExchangeRateCadController;
use App\Http\Controllers\ExchangeRateGbpController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test-route', function () {
    return 'Route works!';
});

Route::get('/exchange-rate/usd-lkr', [ExchangeRateController::class, 'getUsdToLkr']);
Route::get('/insert-sample', [ExchangeRateController::class, 'insertSample']);
Route::get('/exchange-rate/insert-last-7-days', [ExchangeRateController::class, 'insertLast7DaysSampleRates']);

// corrected new api 
Route::get('/exchange-rate/last-7-days', [ExchangeRateController::class, 'getLast7DaysRatesAndAverage']);
Route::get('/exchange-rate/usd-lkr/by-date', [ExchangeRateController::class, 'getUsdToLkrByDate']);
Route::post('/exchange-rate/store', [ExchangeRateController::class, 'storeExchangeRate']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::middleware('auth')->get('/me', [AuthController::class, 'me']);

Route::prefix('aud-rate')->group(function () {
    Route::post('/store', [ExchangeRateAudController::class, 'store']); // Store or update
    Route::get('/last-7-days', [ExchangeRateAudController::class, 'getLast7Days']); // Last 7 days + average
    Route::get('/by-date', [ExchangeRateAudController::class, 'getByDate']); // Get by specific date
});

Route::prefix('cad-rate')->group(function () {
    Route::post('/store', [ExchangeRateCadController::class, 'store']);
    Route::get('/last-7-days', [ExchangeRateCadController::class, 'getLast7Days']);
    Route::get('/by-date', [ExchangeRateCadController::class, 'getByDate']);
});



Route::prefix('gbp-rate')->group(function () {
    Route::post('/store', [ExchangeRateGbpController::class, 'store']);
    Route::get('/last-7-days', [ExchangeRateGbpController::class, 'getLast7Days']);
    Route::get('/by-date', [ExchangeRateGbpController::class, 'getByDate']);
});

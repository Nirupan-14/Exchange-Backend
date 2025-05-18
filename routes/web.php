<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\AuthController;

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

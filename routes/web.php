<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ListViewController;
use App\Http\Controllers\MesinController;

// Redirect root ke login
Route::get('/', function () {
    return redirect('/login');
});

// Routes publik tanpa middleware
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/auth', [LoginController::class, 'login'])->name('auth');

// Routes yang butuh login (middleware checklogin)
Route::middleware(['checklogin'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/calculation', [HomeController::class, 'calculation'])->name('home.calculation');
    Route::post('/saveData', [HomeController::class, 'store'])->name('schedule.store');

    Route::get('/loadMesin', [MesinController::class, 'index'])->name('loadMesin');

    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('/data_filter', [ScheduleController::class, 'dataFilter'])->name('schedule.filterdata');

    Route::get('/view2', [ListViewController::class, 'index'])->name('view2');
    Route::get('/Po-List', [ListViewController::class, 'poList'])->name('poList');
    Route::get('/Schedule-List', [ListViewController::class, 'scheList'])->name('scheList');
    Route::post('/Edit-Schedule', [ListViewController::class, 'editSchedule'])->name('editSchedule');
    Route::get('/Forecast-List', [ListViewController::class, 'forecastList'])->name('forecastList');
    Route::get('/search/{item_code}', [ListViewController::class, 'searchForecast'])->name('search');
    Route::get('/load-data', [ListViewController::class, 'loadData'])->name('loadData');
    Route::post('/get-stock-detail', [ListViewController::class, 'getStockDetail'])->name('getDetailStock');
    
    Route::get('/view', [ListViewController::class, 'index2'])->name('view');
    Route::get('/schedule/{item_code}', [ListViewController::class, 'getScheduleByItemCode'])->name('viewItem');

    Route::get('/api/items', [ListViewController::class, 'getItems']);
    Route::get('/api/items/{itemCode}/detail', [ListViewController::class, 'getItemDetail']);

});

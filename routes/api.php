<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ListViewController;
use App\Http\Controllers\KnittingController;

Route::get('/items', [ListViewController::class, 'getItems']);
Route::get('/items/{itemCode}/detail', [ListViewController::class, 'getItemDetail']);

Route::post('/create', [LoginController::class, 'createAccount'])->name('createAccount');

Route::get('/get-data', [KnittingController::class, 'dataGet'])->name('dataGet');


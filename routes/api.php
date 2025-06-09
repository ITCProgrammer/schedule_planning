<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

use App\Http\Controllers\ListViewController;

Route::get('/items', [ListViewController::class, 'getItems']);
Route::get('/items/{itemCode}/detail', [ListViewController::class, 'getItemDetail']);

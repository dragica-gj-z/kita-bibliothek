<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AddBooksController;
use App\Http\Controllers\ShowBooksController;
// use Illuminate\View\View; // nur nötig, wenn du den Rückgabetyp annotierst

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('/api/add-book',  [AddBooksController::class, 'addBook']);
Route::get ('/api/show-books',[ShowBooksController::class, 'showBooks']);


Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');


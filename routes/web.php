<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddBooksController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::post('api/add-book', [AddBooksController::class, 'addBook']);



Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

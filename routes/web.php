<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\MediaController;

Route::get('/', function () {
    return view('welcome');
});

// Student
Route::post('/student', [StudentController::class, 'store']);
Route::get('/student', [StudentController::class, 'index']);
Route::put('/student', [StudentController::class, 'update']);
Route::delete('/student', [StudentController::class, 'destroy']);
Route::patch('/student', [StudentController::class, 'restore']);


// Media
Route::post('/media', [MediaController::class, 'store']);
Route::get('/media', [MediaController::class, 'index']);
Route::put('/media/{id}', [MediaController::class, 'update']);
Route::delete('/media/{id}', [MediaController::class, 'destroy']);
Route::patch('/media/{id}', [MediaController::class, 'restore']);

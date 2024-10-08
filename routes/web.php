<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/student', [StudentController::class, 'store']);
Route::get('/student', [StudentController::class, 'index']);
Route::put('/student', [StudentController::class, 'update']);
Route::delete('/student', [StudentController::class, 'destroy']);
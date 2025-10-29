<?php

use App\Http\Controllers\Api\LayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/layers', [LayerController::class, 'index']); 

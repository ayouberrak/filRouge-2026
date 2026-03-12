<?php

use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\AuthController;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Test routes demonstrating the Middlewares
    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Welcome Admin!']);
    })->middleware(['status.active', 'role.admin']);

    Route::get('/ ', function () {
        return response()->json(['message' => 'Welcome Formateur!']);
    })->middleware(['status.active', 'role.formateur']);

    Route::get('/student/dashboard', function () {
        return response()->json(['message' => 'Welcome Student!']);
    })->middleware(['status.active', 'role.student']);
});
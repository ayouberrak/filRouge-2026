<?php

use App\Modules\Classroom\Http\Controllers\ClassroomController;
use App\Modules\User\Http\Controllers\UserController;
use App\Modules\Squad\Http\Controllers\SquadController;
use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\AuthController;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });



    Route::middleware(['status.active', 'role.formateur'])->group(function () {
        Route::get('/formateur/dashboard', function () {
            return response()->json(['message' => 'Welcome Formateur!']);
        });


        // Squad Routes
        Route::prefix('squads')->group(function () {
            Route::get('/', [SquadController::class, 'index']);
            Route::get('/{id}', [SquadController::class, 'show']);
            Route::post('/create', [SquadController::class, 'create']);
            Route::post('/{id}/members', [SquadController::class, 'assignMember']);
            Route::delete('/{id}/members/{userId}', [SquadController::class, 'removeMember']);
        });
    });


    Route::middleware(['status.active', 'role.admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Welcome Admin!']);
        });

        // classRoom subRoute
        Route::prefix('classrooms')->group(function () { 
            Route::get('/', [ClassroomController::class, 'index']);
            Route::post('/create', [ClassroomController::class, 'create']);
            Route::get('/{id}', [ClassroomController::class, 'show']);
            Route::delete('/{id}', [ClassroomController::class, 'delete']);
            Route::post('/{id}/assignFormateur', [ClassroomController::class, 'assignFormateur']);
        });

        // User Management Routes
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::post('/create', [UserController::class, 'create']);
            Route::put('/update/{id}', [UserController::class, 'update']);
            Route::patch('/ban/{id}', [UserController::class, 'ban']);
        });

    });


    Route::middleware(['status.active', 'role.student'])->group(function () {
        Route::get('/student/dashboard', function () {
            return response()->json(['message' => 'Welcome Student!']);
        });
    });


});

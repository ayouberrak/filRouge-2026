<?php

use App\Modules\Classroom\Http\Controllers\ClassroomController;
use App\Modules\User\Http\Controllers\UserController;
use App\Modules\Squad\Http\Controllers\SquadController;
use App\Modules\Absence\Http\Controllers\AbsenceController;
use App\Modules\Report\Http\Controllers\DailyReportController;
use App\Modules\Marketplace\Http\Controllers\MarketplaceController;
use App\Modules\User\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Modules\Brief\Http\Controllers\BriefController; 
use App\Modules\Livrable\Http\Controllers\LivrableController;
use App\Modules\Activity\Http\Controllers\ActivityController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Shared Brief Routes (Read-only for Students, Full for Formateurs/Admins)
    Route::prefix('briefs')->group(function () {
        Route::get('/', [BriefController::class, 'index']);
        Route::get('/{id}', [BriefController::class, 'show']);
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

        // Absence Routes for Formateur
        Route::prefix('absences')->group(function () {
            Route::get('/student/{studentId}', [AbsenceController::class, 'getByStudent']);
            Route::get('/classroom/{classroomId}', [AbsenceController::class, 'getByClassroom']);
            Route::post('/create', [AbsenceController::class, 'create']);
        });

        // Brief Routes
        Route::prefix('briefs')->group(function () {
            Route::post('/', [BriefController::class, 'store']);
            Route::put('/{id}', [BriefController::class, 'update']);
            Route::post('/{id}/assign-classrooms', [BriefController::class, 'assignClassrooms']);
        });
        // Livrable Routes for Formateur
        Route::prefix('livrables')->group(function () {
            Route::post('/{id}/reponse', [LivrableController::class, 'addReponse']);
            Route::get('/{id}', [LivrableController::class, 'show']);
        });

        // Activity Routes for Formateur
        Route::prefix('activities')->group(function () {
            Route::post('/', [ActivityController::class, 'store']);
            Route::post('/{id}/assign', [ActivityController::class, 'assign']);
        });

        // Report Routes for Formateur
        Route::prefix('reports')->group(function () {
            Route::post('/', [DailyReportController::class, 'store']);
        });
    });


    Route::middleware(['status.active', 'role.admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Welcome Admin!']);
        });

        // Admin Group Tasks (Validation Livrable, View Reports)
        Route::get('/reports', [DailyReportController::class, 'index']);
        Route::get('/reports/classroom/{classroomId}', [DailyReportController::class, 'getByClassroom']);

        // Dashboard Stats
        Route::get('/dashboard/stats', [AnalyticsController::class, 'getStats']);

        // Marketplace Routes for Admin
        Route::prefix('admin/marketplace')->group(function () {
            Route::get('/orders', [MarketplaceController::class, 'indexOrders']);
            Route::post('/products', [MarketplaceController::class, 'storeProduct']);
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

        // Absence Routes for Admin
        Route::prefix('absences')->group(function () {
            Route::get('/', [AbsenceController::class, 'index']);
            Route::patch('/{id}/approve', [AbsenceController::class, 'approve']);
            Route::patch('/{id}/reject', [AbsenceController::class, 'reject']);
        });

    });


    Route::middleware(['status.active', 'role.student'])->group(function () {
        Route::get('/student/dashboard', function () {
            return response()->json(['message' => 'Welcome Student!']);
        });

        // Absence Routes for Student
        Route::prefix('absences')->group(function () {
            Route::get('/my', [AbsenceController::class, 'myAbsences']);
            Route::post('/{id}/justify', [AbsenceController::class, 'justify']);
        });

        // Activity Routes for Student
        Route::prefix('activities')->group(function () {
            Route::get('/me', [ActivityController::class, 'getMyActivities']);
            Route::get('/classroom/{classroomId}', [ActivityController::class, 'getByClassroom']);
        });
        // Livrable Routes for Student
        Route::prefix('livrables')->group(function () {
            Route::post('/', [LivrableController::class, 'store']);
        });

        // Marketplace Routes for Student
        Route::prefix('marketplace')->group(function () {
            Route::get('/products', [MarketplaceController::class, 'indexProducts']);
            Route::post('/purchase/{id}', [MarketplaceController::class, 'purchase']);
        });

        // Leaderboard
        Route::get('/leaderboard', [AnalyticsController::class, 'getLeaderboard']);
    });


});

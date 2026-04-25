<?php

use App\Modules\Classroom\Http\Controllers\ClassroomController;
use App\Modules\User\Http\Controllers\UserController;
use App\Modules\Squad\Http\Controllers\SquadController;
use App\Modules\Absence\Http\Controllers\AbsenceController;
use App\Modules\Report\Http\Controllers\DailyReportController;

use App\Modules\User\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Modules\Brief\Http\Controllers\BriefController;
use App\Modules\Livrable\Http\Controllers\LivrableController;
use App\Modules\Activity\Http\Controllers\ActivityController;
use App\Modules\Quiz\Http\Controllers\QuizController;
use App\Modules\Chat\Http\Controllers\ChatController;

use App\Modules\User\Http\Controllers\PasswordResetController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    // Re-register Broadcast routes for Sanctum
    Broadcast::routes(['middleware' => ['auth:sanctum']]);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    Route::get('/leaderboard', [AnalyticsController::class, 'getLeaderboard']);

    // Chat Routes
    Route::prefix('chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'index']);
        Route::get('/conversations/{id}/messages', [ChatController::class, 'show']);
        Route::post('/messages', [ChatController::class, 'store']);
        Route::post('/conversations', [ChatController::class, 'start']);
        Route::get('/users/search', [ChatController::class, 'search']);
    });

    // Analytics & Student Management Routes
    Route::prefix('analytics')->group(function () {
        Route::get('/student/stats', [AnalyticsController::class, 'getStudentStats']);
        Route::get('/students', [AnalyticsController::class, 'getStudents']);
        Route::get('/students/{id}', [AnalyticsController::class, 'getStudentProfile']);
    });
    Route::get('/squads/my', [SquadController::class, 'mySquad']);

    // Shared Brief Routes (Read-only for Students, Full for Formateurs/Admins)
    Route::prefix('briefs')->group(function () {
        Route::get('/', [BriefController::class, 'index']);
        Route::get('/{id}', [BriefController::class, 'show']);
        Route::get('/{id}/submissions', [LivrableController::class, 'listByBrief']);
    });

    // Activities Shared Routes
    Route::get('/activities/classroom/{classroomId}', [ActivityController::class, 'getByClassroom']);


    // Dashboard Stats (Shared between Admin & Formateur)
    Route::get('/dashboard/stats', [AnalyticsController::class, 'getStats']);


    Route::middleware(['status.active', 'role.formateur'])->group(function () {
        Route::get('/formateur/dashboard', function () {
            return response()->json(['message' => 'Welcome Formateur!']);
        });

        // Squad Routes
        Route::prefix('squads')->group(function () {
            Route::get('/', [SquadController::class, 'index']);
            Route::get('/{id}', [SquadController::class, 'show']);
            Route::post('/create', [SquadController::class, 'create']);
            Route::delete('/{id}', [SquadController::class, 'delete']);
            Route::post('/{id}/members', [SquadController::class, 'assignMember']);
            Route::delete('/{id}/members/{userId}', [SquadController::class, 'removeMember']);
        });

        // Absence Routes for Formateur
        Route::prefix('absences')->group(function () {
            Route::get('/student/{studentId}', [AbsenceController::class, 'getByStudent']);
            Route::get('/classroom/{classroomId}', [AbsenceController::class, 'getByClassroom']);
            Route::post('/create', [AbsenceController::class, 'create']);
            Route::delete('/{id}', [AbsenceController::class, 'delete']);
        });

        // Brief Routes
        Route::prefix('briefs')->group(function () {
            Route::post('/', [BriefController::class, 'store']);
            Route::put('/{id}', [BriefController::class, 'update']);
            Route::post('/{id}/assign-classrooms', [BriefController::class, 'assignClassrooms']);
            Route::post('/{id}/assign-squads', [BriefController::class, 'assignSquads']);
        });
        // Formateur's own classrooms
        Route::get('/classrooms/my', [ClassroomController::class, 'myClassrooms']);

        // Livrable Routes for Formateur
        Route::prefix('livrables')->group(function () {
            Route::post('/{id}/reponse', [LivrableController::class, 'addReponse']);
            Route::get('/{id}', [LivrableController::class, 'show']);
        });

        // Activity Routes for Formateur
        Route::prefix('activities')->group(function () {
            Route::post('/', [ActivityController::class, 'store']);
            Route::post('/{id}/assign', [ActivityController::class, 'assign']);
            Route::post('/{id}/assign-classroom', [ActivityController::class, 'assignClassroom']);
        });

        // Shared Report Routes (Accessible by Formateurs and Admins)
        Route::prefix('reports')->group(function () {
            Route::get('/', [DailyReportController::class, 'index']);
            Route::get('/classroom/{classroomId}', [DailyReportController::class, 'getByClassroom']);
            Route::get('/stats/{classroomId}', [DailyReportController::class, 'getStats']);
            Route::post('/', [DailyReportController::class, 'store'])->middleware('role.formateur');
        });

        Route::prefix('quizzes')->group(function () {
            Route::post('/sessions', [QuizController::class, 'createSession']);
            Route::post('/sessions/{sessionId}/start', [QuizController::class, 'startSession']);
            Route::get('/sessions/{sessionId}/students/{studentId}/responses', [QuizController::class, 'getStudentResponses']);
            Route::get('/briefs/{briefId}/session', [QuizController::class, 'getSessionByBrief']);
            Route::get('/debug-ping', function () {
                return response()->json(['ping' => 'pong', 'user' => Auth::id()]); });
        });
    });

    Route::get('/quizzes/briefs/{briefId}/validate', [\App\Modules\Quiz\Http\Controllers\QuizController::class, 'validateBriefCompletion']);

        Route::middleware(['status.active', 'role.admin'])->group(function () {
        Route::get('/admin/dashboard', [AnalyticsController::class, 'getAdminStats']);
        Route::get('/admin/stats', [AnalyticsController::class, 'getAdminStats']);

        // Daily Reports (read-only for admin)
        Route::prefix('reports')->group(function () {
            Route::get('/', [DailyReportController::class, 'index']);
            Route::get('/classroom/{classroomId}', [DailyReportController::class, 'getByClassroom']);
            Route::get('/stats/{classroomId}', [DailyReportController::class, 'getStats']);
        });



        // classRoom subRoute
        Route::prefix('classrooms')->group(function () {
            Route::get('/', [ClassroomController::class, 'index']);
            Route::post('/create', [ClassroomController::class, 'create']);
            Route::get('/{id}', [ClassroomController::class, 'show']);
            Route::delete('/{id}', [ClassroomController::class, 'delete']);
            Route::post('/{id}/assign-formateur', [ClassroomController::class, 'assignFormateur']);
            Route::post('/{id}/assign-students', [ClassroomController::class, 'assignStudents']);
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
        Route::get('/student/dashboard', [AnalyticsController::class, 'getStudentStats']);

        // Absence Routes for Student
        Route::prefix('absences')->group(function () {
            Route::get('/my', [AbsenceController::class, 'myAbsences']);
            Route::post('/{id}/justify', [AbsenceController::class, 'justify']);
        });

        // Activity Routes for Student
        Route::prefix('activities')->group(function () {
            Route::get('/me', [ActivityController::class, 'getMyActivities']);
        });
        // Livrable Routes for Student
        Route::prefix('livrables')->group(function () {
            Route::get('/', [LivrableController::class, 'index']);
            Route::post('/', [LivrableController::class, 'store']);
        });

        // Quiz Routes for Student
        Route::prefix('quizzes')->group(function () {
            Route::get('/briefs/{briefId}/session', [QuizController::class, 'getSessionByBrief']);
            Route::get('/sessions/{id}/questions', [QuizController::class, 'getQuestions']);
            Route::post('/responses', [QuizController::class, 'submitResponse']);
        });
    });

});

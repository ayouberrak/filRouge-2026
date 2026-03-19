<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
use App\Modules\Absence\Infrastructure\Models\AbsenceModel;
use Illuminate\Http\JsonResponse;

class AnalyticsController
{
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_students' => UserModel::where('role', 'student')->count(),
            'active_classrooms' => ClassroomModel::count(),
            'pending_deliverables' => LivrableModel::where('status', 'PENDING')->count(),
            'absences_today' => AbsenceModel::whereDate('date', now()->toDateString())->count(),
        ];

        return response()->json(['data' => $stats]);
    }

    public function getLeaderboard(): JsonResponse
    {
        $leaderboard = UserModel::where('role', 'student')
            ->orderBy('total_points', 'desc')
            ->take(10)
            ->get(['id', 'first_name', 'last_name', 'total_points']);

        return response()->json(['data' => $leaderboard]);
    }
}

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
            'total_staff' => UserModel::where('role', 'formateur')->count(),
            'active_classrooms' => ClassroomModel::count(),
            'pending_deliverables' => LivrableModel::where('status', 'PENDING')->count(),
            'absences_today' => AbsenceModel::whereDate('date', now()->toDateString())->count(),
        ];

        return response()->json(['data' => $stats], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function getAdminStats(): JsonResponse
    {
        $stats = [
            'total_users' => UserModel::count(),
            'total_students' => UserModel::where('role', 'student')->count(),
            'total_staff' => UserModel::where('role', 'formateur')->count(),
            'active_classrooms' => ClassroomModel::count(),
            'absences_today' => AbsenceModel::whereDate('date', now()->toDateString())->count(),
            'pending_justifications' => AbsenceModel::where('status', 'PENDING')->whereNotNull('justification_file')->count(),
            'recent_activity' => $this->getGlobalRecentActivity()
        ];

        return response()->json(['data' => $stats], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    private function getGlobalRecentActivity()
    {
        $absences = AbsenceModel::with('student')->latest()->take(3)->get()->map(fn($a) => [
            'type' => 'absence',
            'user' => ($a->student->first_name ?? 'N/A') . ' ' . ($a->student->last_name ?? ''),
            'time' => $a->created_at->diffForHumans(),
            'label' => 'Signalement d\'absence'
        ]);

        $submissions = LivrableModel::with(['student', 'brief'])->latest()->take(3)->get()->map(fn($l) => [
            'type' => 'submission',
            'user' => ($l->student->first_name ?? 'N/A') . ' ' . ($l->student->last_name ?? ''),
            'time' => $l->created_at->diffForHumans(),
            'label' => 'Rendu du brief: ' . ($l->brief->title ?? 'N/A')
        ]);

        return $absences->concat($submissions)->sortByDesc('time')->take(5)->values();
    }

    public function getLeaderboard(): JsonResponse
    {
        $leaderboard = UserModel::where('role', 'student')
            ->withCount(['livrables as validated_briefs_count' => function($query) {
                $query->whereIn('status', ['VALIDATED', 'VALIDE']);
            }])
            ->orderBy('validated_briefs_count', 'desc')
            ->take(8)
            ->get(['id', 'first_name', 'last_name', 'avatar_url', 'location']);

        return response()->json(['data' => $leaderboard], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function getStudents(\Illuminate\Http\Request $request): JsonResponse
    {
        $role = $request->get('role', 'student');
        $query = UserModel::where('role', $role);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('speciality') && $request->speciality !== 'ALL') {
            $query->where('speciality', $request->speciality);
        }

        if ($request->has('classroom_id')) {
            $query->where('classroom_id', $request->classroom_id);
        }

        $students = $query->withCount(['livrables as validated_briefs_count' => function($q) {
                $q->whereIn('status', ['VALIDATED', 'VALIDE']);
            }])
            ->orderBy('first_name', 'asc')
            ->get([
                'id', 'first_name', 'last_name', 'email', 
                'avatar_url', 'location', 'status', 'squad_id', 'speciality',
                'bio', 'skills', 'github_url', 'linkedin_url'
            ]);

        return response()->json(['data' => $students], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function getStudentProfile($id): JsonResponse
    {
        $user = UserModel::with(['squad.members'])->findOrFail($id);
        
        $validatedCount = LivrableModel::where('student_id', $user->id)->whereIn('status', ['VALIDATED', 'VALIDE'])->count();

        $stats = [
            'absences_count' => AbsenceModel::where('student_id', $user->id)->count(),
            'validated_briefs' => $validatedCount,
            'rank' => UserModel::where('role', 'student')
                ->whereHas('livrables', function($q) {
                    $q->whereIn('status', ['VALIDATED', 'VALIDE']);
                }, '>', $validatedCount)
                ->count() + 1,
        ];

        return response()->json([
            'user' => $user,
            'stats' => $stats
        ]);
    }

    public function getStudentStats(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();
        $validatedCount = LivrableModel::where('student_id', $user->id)->whereIn('status', ['VALIDATED', 'VALIDE'])->count();

        $stats = [
            'absences_count' => AbsenceModel::where('student_id', $user->id)->count(),
            'validated_briefs' => $validatedCount,
            'rank' => UserModel::where('role', 'student')
                ->whereHas('livrables', function($q) {
                    $q->whereIn('status', ['VALIDATED', 'VALIDE']);
                }, '>', $validatedCount)
                ->count() + 1,
        ];

        return response()->json(['stats' => $stats]);
    }
}

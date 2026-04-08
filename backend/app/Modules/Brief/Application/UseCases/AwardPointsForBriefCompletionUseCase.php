<?php

namespace App\Modules\Brief\Application\UseCases;

use App\Modules\Quiz\Application\UseCases\ValidateBriefCompletionUseCase;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AwardPointsForBriefCompletionUseCase
{
    public function __construct(
        private ValidateBriefCompletionUseCase $validateBriefCompletionUseCase
    ) {}

    public function execute(int $briefId, int $studentId): bool
    {
        return DB::transaction(function () use ($briefId, $studentId) {
            // 1. Check if points already awarded for this student/brief
            $livrable = DB::table('livrables')
                ->where('brief_id', $briefId)
                ->where('student_id', $studentId)
                ->first();

            if (!$livrable) {
                Log::warning("AwardPoints: No livrable found for Brief $briefId and Student $studentId");
                return false;
            }

            if ($livrable->points_awarded) {
                return false; // Already awarded
            }

            // 2. Check overall completion status using existing logic
            $status = $this->validateBriefCompletionUseCase->execute($briefId, $studentId);

            if ($status['status'] === 'VALIDATED') {
                // 3. Get points from brief
                $brief = BriefModel::find($briefId);
                if (!$brief) {
                    Log::warning("AwardPoints: Brief $briefId not found");
                    return false;
                }

                $points = $brief->points ?? 0;
                Log::info("AwardPoints: Calculated $points points for Student $studentId on Brief $briefId");

                /** @var UserModel $user */
                $user = UserModel::find($studentId);
                if ($user) {
                    $user->total_points += $points;
                    $user->save();

                    // 5. Mark as awarded
                    DB::table('livrables')
                        ->where('id', $livrable->id)
                        ->update(['points_awarded' => true]);

                    Log::info("AwardPoints: SUCCESSFULLY AWARDED $points points to student $studentId for brief $briefId");
                    return true;
                } else {
                    Log::error("AwardPoints: Student $studentId not found during award phase");
                }
            } else {
                Log::info("AwardPoints: Status for Student $studentId on Brief $briefId is not VALIDATED but " . $status['status']);
            }

            return false;
        });
    }
}

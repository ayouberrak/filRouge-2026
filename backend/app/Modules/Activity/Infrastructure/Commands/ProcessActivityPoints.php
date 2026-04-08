<?php

namespace App\Modules\Activity\Infrastructure\Commands;

use Illuminate\Console\Command;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessActivityPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:process-points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically award points to students after an activity has ended';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Trouver les activités terminées dont les points n'ont pas été distribués
        // Une activité est terminée si (scheduled_at + duration_minutes) < NOW
        $activities = ActivityModel::where('is_points_distributed', false)
            ->whereNotNull('scheduled_at')
            ->whereNotNull('duration_minutes')
            ->get();

        $processedCount = 0;

        foreach ($activities as $activity) {
            $endTime = $activity->scheduled_at->copy()->addMinutes($activity->duration_minutes);

            if ($now->greaterThan($endTime)) {
                $this->info("Processing activity: {$activity->title} (ID: {$activity->id})");

                // 2. Récupérer les étudiants associés à cette activité (table activity_student)
                $students = $activity->students;

                if ($students->isEmpty()) {
                    $this->warn("No students found for activity: {$activity->title}");
                }

                DB::transaction(function () use ($activity, $students) {
                    foreach ($students as $student) {
                        // Ajouter les points à l'étudiant
                        $student->increment('total_points', $activity->points);
                        $this->line("Awarded {$activity->points} pts to student: {$student->first_name} {$student->last_name}");
                    }

                    // Marquer l'activité comme traitée
                    $activity->update(['is_points_distributed' => true]);
                });

                $processedCount++;
            }
        }

        if ($processedCount > 0) {
            $this->info("Successfully processed points for {$processedCount} activities.");
        } else {
            $this->comment("No activities pending point distribution.");
        }
    }
}

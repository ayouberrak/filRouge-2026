<?php

namespace App\Modules\Brief\Application\Listeners;

use App\Modules\Brief\Domain\Events\BriefAssignedToClassrooms;
use App\Modules\Brief\Application\Mails\BriefAssignedMail;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Classroom\Infrastructure\Models\ClassroomModel;
use Illuminate\Support\Facades\Mail;

class SendBriefAssignedNotification
{
    public function handle(BriefAssignedToClassrooms $event): void
    {
        $brief = BriefModel::find($event->briefId);
        if (!$brief) return;

        $classrooms = ClassroomModel::with('students')->whereIn('id', $event->classroomIds)->get();

        foreach ($classrooms as $classroom) {
            foreach ($classroom->students as $student) {
                if ($student->email) {
                    Mail::to($student->email)->send(new BriefAssignedMail($brief));
                }
            }
        }
    }
}

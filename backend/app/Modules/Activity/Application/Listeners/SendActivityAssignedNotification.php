<?php

namespace App\Modules\Activity\Application\Listeners;

use App\Modules\Activity\Domain\Events\ActivityAssignedToStudents;
use App\Modules\Activity\Application\Mails\ActivityAssignedMail;
use App\Modules\Activity\Infrastructure\Models\ActivityModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\Mail;

class SendActivityAssignedNotification
{
    public function handle(ActivityAssignedToStudents $event): void
    {
        $activity = ActivityModel::find($event->activityId);
        if (!$activity) {
            return;
        }

        $students = UserModel::whereIn('id', $event->studentIds)->get();

        foreach ($students as $student) {
            if ($student->email) {
                Mail::to($student->email)->send(new ActivityAssignedMail($activity));
            }
        }
    }
}

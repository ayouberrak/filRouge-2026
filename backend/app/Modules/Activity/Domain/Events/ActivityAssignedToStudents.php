<?php

namespace App\Modules\Activity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityAssignedToStudents
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $activityId,
        public array $studentIds
    ) {}
}

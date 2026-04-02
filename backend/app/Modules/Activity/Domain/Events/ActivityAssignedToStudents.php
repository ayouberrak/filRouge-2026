<?php

namespace App\Modules\Activity\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityAssignedToStudents
{
    use Dispatchable, SerializesModels;

    /**
     * @param int $activityId
     * @param int[] $studentIds
     */
    public function __construct(
        public int $activityId,
        public array $studentIds
    ) {}
}

<?php

namespace App\Modules\Brief\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefAssignedToClassrooms
{
    use Dispatchable, SerializesModels;

    /**
     * @param int $briefId
     * @param int[] $classroomIds
     */
    public function __construct(
        public int $briefId,
        public array $classroomIds
    ) {}
}

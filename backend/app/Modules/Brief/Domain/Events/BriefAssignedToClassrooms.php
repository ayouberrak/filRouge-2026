<?php

namespace App\Modules\Brief\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BriefAssignedToClassrooms
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $briefId,
        public array $classroomIds
    ) {}
}

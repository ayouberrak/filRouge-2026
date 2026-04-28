<?php

namespace App\Modules\Activity\Application\DTO;

class ActivityDTO
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public string $type,
        public ?string $date_start = null,
        public ?string $date_end = null
    ) {}
}

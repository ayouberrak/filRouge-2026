<?php

namespace App\Modules\Activity\Application\DTO;

class ActivityDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly string $type,
        public readonly ?string $date_start = null,
        public readonly ?string $date_end = null
    ) {}
}

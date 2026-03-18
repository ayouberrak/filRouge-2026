<?php

namespace App\Modules\Brief\Application\DTO;

class BriefDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly string $date_start,
        public readonly string $date_end,
        public readonly ?string $file = null,
        public readonly ?int $formateur_id = null
    ) {}
}

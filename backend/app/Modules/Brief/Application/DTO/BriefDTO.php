<?php

namespace App\Modules\Brief\Application\DTO;

class BriefDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $objectives,
        public readonly string $date_start,
        public readonly string $date_end,
        public readonly string $difficulty,
        public readonly string $modality,
        public readonly string $status,
        public readonly ?array $tags = [],
        public readonly ?array $resources = [],
        public readonly ?string $file = null,
        public readonly ?int $formateur_id = null
    ) {}
}

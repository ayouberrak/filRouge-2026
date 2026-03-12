<?php

namespace App\Modules\Squad\Application\DTO;

class SquadDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $classroom_id = null
    ) {}
}

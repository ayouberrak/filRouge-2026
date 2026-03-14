<?php

namespace App\Modules\Squad\Application\DTO;

class SquadDTO
{
    public function __construct(
        public string $name,
        public ?int $classroom_id = null
    ) {}
}

<?php

namespace App\Modules\Squad\Application\DTO;

class CreateSquadDTO
{
    public function __construct(
        public string $name,
        public int $classroom_id,
        public array $members = []
    ) {}
}

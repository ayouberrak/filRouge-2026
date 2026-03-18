<?php

namespace App\Modules\Squad\Application\DTO;

class AssignMemberDTO
{
    public function __construct(
        public int $squad_id,
        public int $user_id
    ) {}
}

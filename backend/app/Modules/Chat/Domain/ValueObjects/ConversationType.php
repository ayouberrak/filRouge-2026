<?php

namespace App\Modules\Chat\Domain\ValueObjects;

class ConversationType
{
    const INDIVIDUAL = 'individual';
    const SQUAD = 'squad';
    const CLASSROOM = 'classroom';

    public static function getTypes(): array
    {
        return [
            self::INDIVIDUAL,
            self::SQUAD,
            self::CLASSROOM,
        ];
    }
}
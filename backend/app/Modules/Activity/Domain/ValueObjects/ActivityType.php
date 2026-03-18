<?php

namespace App\Modules\Activity\Domain\ValueObjects;

use InvalidArgumentException;

class ActivityType
{
    private string $value;

    private const VALID_TYPES = ['live_coding', 'veille', 'workshop', 'quiz'];

    public function __construct(string $value)
    {
        if (!in_array($value, self::VALID_TYPES)) {
            throw new InvalidArgumentException("Invalid activity type: {$value}");
        }
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

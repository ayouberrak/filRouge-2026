<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;

class BriefStatus
{
    private string $value;

    private const STATUSES = [
        'DRAFT', 'PUBLISHED', 'IN_PROGRESS', 'COMPLETED'
    ];

    public function __construct(string $value)
    {
        $valueee = strtoupper($value);
        if (!in_array($valueee, self::STATUSES, true)) {
            throw new InvalidArgumentException("invalid status");
        }

        $this->value = $valueee;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function draft(): self {
        return new self('DRAFT'); 
    }
    public static function published(): self {
        return new self('PUBLISHED'); 
    }
    public static function inProgress(): self {
        return new self('IN_PROGRESS'); 
    }
    public static function completed(): self {
        return new self('COMPLETED'); 
    }
}

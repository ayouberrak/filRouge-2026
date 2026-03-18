<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;

class BriefStatus
{
    private string $value;

    private const ALLOWED_STATUSES = [
        'DRAFT', 'PUBLISHED', 'IN_PROGRESS', 'COMPLETED', 'ARCHIVED'
    ];

    public function __construct(string $value)
    {
        $upperValue = strtoupper($value);
        if (!in_array($upperValue, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid brief status. Allowed values: " . implode(', ', self::ALLOWED_STATUSES));
        }

        $this->value = $upperValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function draft(): self { return new self('DRAFT'); }
    public static function published(): self { return new self('PUBLISHED'); }
    public static function inProgress(): self { return new self('IN_PROGRESS'); }
    public static function completed(): self { return new self('COMPLETED'); }
    public static function archived(): self { return new self('ARCHIVED'); }
}

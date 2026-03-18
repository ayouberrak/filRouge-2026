<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;

class DifficultyLevel
{
    private string $value;

    private const ALLOWED_LEVELS = ['EASY', 'MEDIUM', 'HARD'];

    public function __construct(string $value)
    {
        $upperValue = strtoupper($value);
        if (!in_array($upperValue, self::ALLOWED_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid difficulty level. Allowed values: " . implode(', ', self::ALLOWED_LEVELS));
        }

        $this->value = $upperValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function easy(): self { return new self('EASY'); }
    public static function medium(): self { return new self('MEDIUM'); }
    public static function hard(): self { return new self('HARD'); }
}

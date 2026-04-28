<?php

namespace App\Modules\Quiz\Domain\ValueObjects;

use InvalidArgumentException;

class QuizStatus
{
    private string $value;

    private const ALLOWED_STATUSES = [
        'PENDING', 'ACTIVE', 'COMPLETED', 'CANCELLED'
    ];

    public function __construct(string $value)
    {
        $upperValue = strtoupper($value);
        if (!in_array($upperValue, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException("error dans le status");
        }

        $this->value = $upperValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function pending(): self { return new self('PENDING'); }
    public static function active(): self { return new self('ACTIVE'); }
    public static function completed(): self { return new self('COMPLETED'); }
    public static function cancelled(): self { return new self('CANCELLED'); }

    public function isCompleted(): bool { return $this->value === 'COMPLETED'; }
    public function isActive(): bool { return $this->value === 'ACTIVE'; }
}

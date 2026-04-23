<?php

namespace App\Modules\Absence\Domain\ValueObjects;

use InvalidArgumentException;

class AbsenceStatus
{
    public const PENDING = 'pending';
    public const JUSTIFIED = 'justified';
    public const REJECTED = 'rejected';
    public const UNJUSTIFIED = 'unjustified';

    public const VALID_STATUSES = [
        self::PENDING,
        self::JUSTIFIED,
        self::REJECTED,
        self::UNJUSTIFIED
    ];

    private string $value;

    public function __construct(string $status)
    {
        $status = strtolower(trim($status));
        
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("invalid absence status");
        }

        $this->value = $status;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(AbsenceStatus $other): bool
    {
        return $this->value === $other->getValue();
    }
}

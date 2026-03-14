<?php

namespace App\Modules\Absence\Domain\ValueObjects;

use InvalidArgumentException;

class AbsenceStatus
{
    public const PENDING = 'pending';
    public const JUSTIFIED = 'justified';
    public const REJECTED = 'rejected';

    private string $value;

    public function __construct(string $status)
    {
        $status = strtolower(trim($status));
        
        if (!in_array($status, [self::PENDING, self::JUSTIFIED, self::REJECTED])) {
            throw new InvalidArgumentException("Invalid absence status: {$status}");
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

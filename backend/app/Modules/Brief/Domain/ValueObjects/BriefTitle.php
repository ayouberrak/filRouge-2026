<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;

class BriefTitle
{
    private string $value;

    public function __construct(string $value)
    {
        $trimValue = trim($value);
        if (empty($trimValue)) {
            throw new InvalidArgumentException("Brief title cannot be empty.");
        }

        if (strlen($trimValue) < 5 || strlen($trimValue) > 255) {
            throw new InvalidArgumentException("Brief title must be between 5 and 255 characters long.");
        }

        $this->value = $trimValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

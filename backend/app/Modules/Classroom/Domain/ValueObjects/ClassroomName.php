<?php

namespace App\Modules\Classroom\Domain\ValueObjects;

use InvalidArgumentException;

class ClassroomName
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException("Classroom name cannot be empty.");
        }

        if (strlen($value) < 3) {
            throw new InvalidArgumentException("Classroom name must be at least 3 characters long.");
        }

        $this->value = $value;
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

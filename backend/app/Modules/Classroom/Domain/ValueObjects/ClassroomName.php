<?php

namespace App\Modules\Classroom\Domain\ValueObjects;

use InvalidArgumentException;

class ClassroomName
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException("name de class est invalide.");
        }

        if (strlen($value) < 3) {
            throw new InvalidArgumentException("name de class doit contenir 3 cara.");
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

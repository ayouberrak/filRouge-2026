<?php

namespace App\Modules\Squad\Domain\ValueObjects;

use InvalidArgumentException;

class SquadName
{
    private string $value;

    public function __construct(string $name)
    {
        $name = trim($name);
        
        if (empty($name)) {
            throw new InvalidArgumentException("error dans le nom du squad");
        }

        if (strlen($name) < 3) {
            throw new InvalidArgumentException("error dans le nom du squad");
        }

        $this->value = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(SquadName $other): bool
    {
        return $this->value === $other->getValue();
    }
}

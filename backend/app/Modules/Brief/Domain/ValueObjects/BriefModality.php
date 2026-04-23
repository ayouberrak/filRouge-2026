<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;

class BriefModality
{
    private string $value;

    private const MODALITIES = ['INDIVIDUAL', 'GROUP'];

    public function __construct(string $value)
    {
        $valuee = strtoupper($value);
        if (!in_array($valuee, self::MODALITIES, true)) {
            throw new InvalidArgumentException("invalid modality");
        }

        $this->value = $valuee;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isGroup(): bool
    {
        return $this->value === 'GROUP';
    }

    public static function individual(): self {
        return new self('INDIVIDUAL'); 
    }
    public static function group(): self {
        return new self('GROUP'); 
    }
}

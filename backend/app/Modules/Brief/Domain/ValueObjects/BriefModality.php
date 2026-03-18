<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;

class BriefModality
{
    private string $value;

    private const ALLOWED_MODALITIES = ['INDIVIDUAL', 'GROUP'];

    public function __construct(string $value)
    {
        $upperValue = strtoupper($value);
        if (!in_array($upperValue, self::ALLOWED_MODALITIES, true)) {
            throw new InvalidArgumentException("Invalid brief modality. Allowed values: " . implode(', ', self::ALLOWED_MODALITIES));
        }

        $this->value = $upperValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isGroup(): bool
    {
        return $this->value === 'GROUP';
    }

    public static function individual(): self { return new self('INDIVIDUAL'); }
    public static function group(): self { return new self('GROUP'); }
}

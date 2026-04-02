<?php

namespace App\Modules\Quiz\Domain\ValueObjects;

use InvalidArgumentException;

class QuestionType
{
    private string $value;

    private const ALLOWED_TYPES = [
        'multiple_choice', 'code_simulation'
    ];

    public function __construct(string $value)
    {
        $lowerValue = strtolower($value);
        if (!in_array($lowerValue, self::ALLOWED_TYPES, true)) {
            throw new InvalidArgumentException("Invalid question type. Allowed values: " . implode(', ', self::ALLOWED_TYPES));
        }

        $this->value = $lowerValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function multipleChoice(): self { return new self('multiple_choice'); }
    public static function codeSimulation(): self { return new self('code_simulation'); }
}

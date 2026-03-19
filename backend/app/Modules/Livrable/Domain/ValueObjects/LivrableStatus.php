<?php

namespace App\Modules\Livrable\Domain\ValueObjects;

use InvalidArgumentException;

class LivrableStatus
{
    private string $status;

    public const SUBMITTED = 'SUBMITTED';
    public const VALIDATED = 'VALIDATED';
    public const REJECTED = 'REJECTED';

    private const VALID_STATUSES = [
        self::SUBMITTED,
        self::VALIDATED,
        self::REJECTED
    ];

    public function __construct(string $status)
    {
        $status = strtoupper($status);
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new InvalidArgumentException("Invalid Livrable status : {$status}");
        }

        $this->status = $status;
    }

    public function getValue(): string
    {
        return $this->status;
    }
}

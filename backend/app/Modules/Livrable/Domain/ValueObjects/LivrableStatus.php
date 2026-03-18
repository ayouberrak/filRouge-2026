<?php

namespace App\Modules\Livrable\Domain\ValueObjects;

use InvalidArgumentException;

class LivrableStatus
{
    private string $status;

    private const VALID_STATUSES = ['soumis', 'validé', 'invalidé'];

    public function __construct(string $status)
    {
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

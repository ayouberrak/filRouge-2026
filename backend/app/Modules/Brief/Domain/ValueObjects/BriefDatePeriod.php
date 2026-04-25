<?php

namespace App\Modules\Brief\Domain\ValueObjects;

use InvalidArgumentException;
use DateTimeImmutable;

class BriefDatePeriod
{
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        
        $this->startDate = new DateTimeImmutable($startDate);
        $this->endDate = new DateTimeImmutable($endDate);
        

        if ($this->endDate < $this->startDate) {
            throw new InvalidArgumentException("date start is after date end");
        }
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getStartDateString(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->startDate->format($format);
    }

    public function getEndDateString(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->endDate->format($format);
    }
}
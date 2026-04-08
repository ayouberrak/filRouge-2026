<?php

namespace App\Modules\Livrable\Application\DTO;

class SubmitLivrableDTO
{
    public readonly int $briefId;
    public readonly ?int $studentId;
    public readonly ?int $squadId;
    public readonly string $link;
    public readonly ?string $message;

    public function __construct(
        int $briefId,
        ?int $studentId,
        ?int $squadId,
        string $link,
        ?string $message = null
    ) {
        $this->briefId = $briefId;
        $this->studentId = $studentId;
        $this->squadId = $squadId;
        $this->link = $link;
        $this->message = $message;
    }
}

<?php

namespace App\Modules\Livrable\Application\DTO;

class SubmitLivrableDTO
{
    public int $briefId;
    public ?int $studentId;
    public ?int $squadId;
    public string $link;
    public ?string $message;

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

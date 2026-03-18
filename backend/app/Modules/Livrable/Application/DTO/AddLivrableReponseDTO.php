<?php

namespace App\Modules\Livrable\Application\DTO;

class AddLivrableReponseDTO
{
    public readonly int $livrableId;
    public readonly int $formateurId;
    public readonly string $status;
    public readonly string $message;

    public function __construct(
        int $livrableId,
        int $formateurId,
        string $status,
        string $message
    ) {
        $this->livrableId = $livrableId;
        $this->formateurId = $formateurId;
        $this->status = $status;
        $this->message = $message;
    }
}

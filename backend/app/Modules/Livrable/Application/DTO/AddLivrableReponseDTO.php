<?php

namespace App\Modules\Livrable\Application\DTO;

class AddLivrableReponseDTO
{
    public int $livrableId;
    public int $formateurId;
    public string $status;
    public string $message;

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

<?php

namespace App\Modules\Livrable\Domain\Entities;

use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;

class ReponseLivrableEntity
{
    private ?int $id;
    private int $livrableId;
    private int $formateurId;
    private LivrableStatus $status;
    private string $message;

    private ?\DateTimeInterface $createdAt;
 
    public function __construct(
        ?int $id,
        int $livrableId,
        int $formateurId,
        LivrableStatus $status,
        string $message,
        ?\DateTimeInterface $createdAt = null
    ) {
        $this->id = $id;
        $this->livrableId = $livrableId;
        $this->formateurId = $formateurId;
        $this->status = $status;
        $this->message = $message;
        $this->createdAt = $createdAt;
    }
 
    public function getId(): ?int { return $this->id; }
    public function getLivrableId(): int { return $this->livrableId; }
    public function getFormateurId(): int { return $this->formateurId; }
    public function getStatus(): LivrableStatus { return $this->status; }
    public function getMessage(): string { return $this->message; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
 
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'livrable_id' => $this->livrableId,
            'formateur_id' => $this->formateurId,
            'status' => $this->status->getValue(),
            'message' => $this->message,
            'created_at' => $this->createdAt ? $this->createdAt->format(\DateTimeInterface::ATOM) : null,
        ];
    }
}

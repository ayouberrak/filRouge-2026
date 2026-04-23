<?php

namespace App\Modules\Livrable\Domain\Entities;

use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;

class LivrableEntity
{
    private ?int $id;
    private int $briefId;
    private ?int $studentId;
    private ?int $squadId;
    private string $link;
    private ?string $message;
    private LivrableStatus $status;
    private ?int $formateurId;
    private ?string $formateurMessage;
    private ?\DateTimeInterface $updatedAt;
    private ?\DateTimeInterface $createdAt;

    public function __construct(
        ?int $id,
        int $briefId,
        ?int $studentId,
        ?int $squadId,
        string $link,
        LivrableStatus $status,
        ?int $formateurId = null,
        ?string $formateurMessage = null,
        ?\DateTimeInterface $updatedAt = null,
        ?string $message = null,
        ?\DateTimeInterface $createdAt = null
    ) {
        $this->id = $id;
        $this->briefId = $briefId;
        $this->studentId = $studentId;
        $this->squadId = $squadId;
        $this->link = $link;
        $this->message = $message;
        $this->status = $status;
        $this->formateurId = $formateurId;
        $this->formateurMessage = $formateurMessage;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getBriefId(): int { return $this->briefId; }
    public function getStudentId(): ?int { return $this->studentId; }
    public function getSquadId(): ?int { return $this->squadId; }
    public function getLink(): string { return $this->link; }
    public function getMessage(): ?string { return $this->message; }
    public function getStatus(): LivrableStatus { return $this->status; }
    public function getFormateurId(): ?int { return $this->formateurId; }
    public function getFormateurMessage(): ?string { return $this->formateurMessage; }
    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    
    public function setStatus(LivrableStatus $status): void { $this->status = $status; }
    public function setFormateurId(?int $formateurId): void { $this->formateurId = $formateurId; }
    public function setFormateurMessage(?string $formateurMessage): void { $this->formateurMessage = $formateurMessage; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'brief_id' => $this->briefId,
            'student_id' => $this->studentId,
            'squad_id' => $this->squadId,
            'link' => $this->link,
            'message' => $this->message,
            'status' => $this->status->getValue(),
            'formateur_id' => $this->formateurId,
            'formateur_message' => $this->formateurMessage,
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}

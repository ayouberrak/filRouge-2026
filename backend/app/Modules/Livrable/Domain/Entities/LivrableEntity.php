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
    private LivrableStatus $status;
    private array $responses; // Array of ReponseLivrableEntity

    public function __construct(
        ?int $id,
        int $briefId,
        ?int $studentId,
        ?int $squadId,
        string $link,
        LivrableStatus $status,
        array $responses = []
    ) {
        $this->id = $id;
        $this->briefId = $briefId;
        $this->studentId = $studentId;
        $this->squadId = $squadId;
        $this->link = $link;
        $this->status = $status;
        $this->responses = $responses;
    }

    public function getId(): ?int { return $this->id; }
    public function getBriefId(): int { return $this->briefId; }
    public function getStudentId(): ?int { return $this->studentId; }
    public function getSquadId(): ?int { return $this->squadId; }
    public function getLink(): string { return $this->link; }
    public function getStatus(): LivrableStatus { return $this->status; }
    public function getResponses(): array { return $this->responses; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'brief_id' => $this->briefId,
            'student_id' => $this->studentId,
            'squad_id' => $this->squadId,
            'link' => $this->link,
            'status' => $this->status->getValue(),
            'responses' => array_map(fn($resp) => $resp->toArray(), $this->responses),
        ];
    }
}

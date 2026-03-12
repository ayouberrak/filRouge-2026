<?php

namespace App\Modules\Absence\Domain\Entities;

class AbsenceEntity
{
    private ?int $id;
    private string $date;
    private int $duree;
    private string $status;
    private ?string $justificationFile;

    public function __construct(
        ?int $id,
        string $date,
        int $duree,
        string $status,
        ?string $justificationFile = null
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->duree = $duree;
        $this->status = $status;
        $this->justificationFile = $justificationFile;
    }

    public function getId(): ?int { return $this->id; }
    public function getDate(): string { return $this->date; }
    public function getDuree(): int { return $this->duree; }
    public function getStatus(): string { return $this->status; }
    public function getJustificationFile(): ?string { return $this->justificationFile; }
}
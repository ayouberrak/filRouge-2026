<?php

namespace App\Modules\Activity\Domain\Entities;

class ActivityEntity
{
    private ?int $id;
    private string $title;
    private ?string $description;
    private string $type;
    private ?string $dateDebut;
    private ?string $dateFin;

    public function __construct(
        ?int $id,
        string $title,
        ?string $description,
        string $type,
        ?string $dateDebut = null,
        ?string $dateFin = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getType(): string { return $this->type; }
    public function getDateDebut(): ?string { return $this->dateDebut; }
    public function getDateFin(): ?string { return $this->dateFin; }
}
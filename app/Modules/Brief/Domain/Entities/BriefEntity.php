<?php

namespace App\Modules\Brief\Domain\Entities;

class BriefEntity
{
    private ?int $id;
    private string $title;
    private string $description;
    private string $dateStart;
    private string $dateEnd;
    private ?string $file;

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        string $dateStart,
        string $dateEnd,
        ?string $file = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
        $this->file = $file;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getDateStart(): string { return $this->dateStart; }
    public function getDateEnd(): string { return $this->dateEnd; }
    public function getFile(): ?string { return $this->file; }
}
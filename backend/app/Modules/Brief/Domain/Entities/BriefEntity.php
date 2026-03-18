<?php

namespace App\Modules\Brief\Domain\Entities;

use App\Modules\Brief\Domain\ValueObjects\BriefTitle;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\DifficultyLevel;

class BriefEntity
{
    private ?int $id;
    private BriefTitle $title;
    private string $description;
    private ?string $objectives;
    private BriefDatePeriod $period;
    private DifficultyLevel $difficulty;
    private BriefModality $modality;
    private BriefStatus $status;
    private array $tags;
    private array $resources;
    private int $formateurId;

    public function __construct(
        ?int $id,
        BriefTitle $title,
        string $description,
        ?string $objectives,
        BriefDatePeriod $period,
        DifficultyLevel $difficulty,
        BriefModality $modality,
        BriefStatus $status,
        array $tags,
        array $resources,
        int $formateurId
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->objectives = $objectives;
        $this->period = $period;
        $this->difficulty = $difficulty;
        $this->modality = $modality;
        $this->status = $status;
        $this->tags = $tags;
        $this->resources = $resources;
        $this->formateurId = $formateurId;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): BriefTitle { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getObjectives(): ?string { return $this->objectives; }
    public function getPeriod(): BriefDatePeriod { return $this->period; }
    public function getDifficulty(): DifficultyLevel { return $this->difficulty; }
    public function getModality(): BriefModality { return $this->modality; }
    public function getStatus(): BriefStatus { return $this->status; }
    public function getTags(): array { return $this->tags; }
    public function getResources(): array { return $this->resources; }
    public function getFormateurId(): int { return $this->formateurId; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title->getValue(),
            'description' => $this->description,
            'objectives' => $this->objectives,
            'date_start' => $this->period->getStartDateString(),
            'date_end' => $this->period->getEndDateString(),
            'difficulty' => $this->difficulty->getValue(),
            'modality' => $this->modality->getValue(),
            'status' => $this->status->getValue(),
            'tags' => json_encode($this->tags),
            'resources' => json_encode($this->resources),
            'formateur_id' => $this->formateurId
        ];
    }
}
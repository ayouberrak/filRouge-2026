<?php

namespace App\Modules\Brief\Domain\Entities;

use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;

class BriefEntity
{
    private ?int $id;
    private string $title;
    private ?string $imageUrl;
    private string $description;
    private ?string $context;
    private BriefDatePeriod $period;
    private BriefModality $modality;
    private BriefStatus $status;
    private array $tags;
    private int $formateurId;
    private bool $hasQuiz;

    public function __construct(?int $id, string $title, ?string $imageUrl, string $description, ?string $context, BriefDatePeriod $period, BriefModality $modality, BriefStatus $status, array $tags = [], int $formateurId, bool $hasQuiz = false) {
        $this->id = $id;
        $this->title = $title;
        $this->imageUrl = $imageUrl;
        $this->description = $description;
        $this->context = $context;
        $this->period = $period;
        $this->modality = $modality;
        $this->status = $status;
        $this->tags = $tags;
        $this->formateurId = $formateurId;
        $this->hasQuiz = $hasQuiz;
    }

    public function getId(): ?int {
         return $this->id; 
    }
    public function getTitle(): string {
         return $this->title; 
    }
    public function getImageUrl(): ?string {
         return $this->imageUrl; 
    }
    public function getDescription(): string {
         return $this->description; 
    }
    public function getContext(): ?string {
         return $this->context; 
    }

    public function getPeriod(): BriefDatePeriod {
         return $this->period; 
    }
    public function getModality(): BriefModality {
         return $this->modality; 
    }
    public function getStatus(): BriefStatus {
         return $this->status; 
    }
    public function getTags(): array {
         return $this->tags; 
    }
    public function getFormateurId(): int {
         return $this->formateurId; 
    }
    public function hasQuiz(): bool {
         return $this->hasQuiz; 
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->imageUrl,
            'description' => $this->description,
            'context' => $this->context,
            'date_start' => $this->period->getStartDateString(),
            'date_end' => $this->period->getEndDateString(),
            'modality' => $this->modality->getValue(),
            'status' => $this->status->getValue(),
            'tags' => $this->tags,
            'formateur_id' => $this->formateurId,
            'has_quiz' => $this->hasQuiz
        ];
    }
}
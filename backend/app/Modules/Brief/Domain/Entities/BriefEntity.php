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
    private ?string $imageUrl;
    private string $description;
    private ?string $context;
    private ?array $objectives;
    private BriefDatePeriod $period;
    private DifficultyLevel $difficulty;
    private BriefModality $modality;
    private ?string $pedagogicalModalities;
    private ?string $evaluationModalities;
    private BriefStatus $status;
    private int $points;
    private array $tags;
    private array $resources;
    private ?array $deliverables;
    private ?array $performanceCriteria;
    private ?array $targetCompetencies;
    private int $formateurId;
    private bool $hasQuiz;

    public function __construct(
        ?int $id,
        BriefTitle $title,
        ?string $imageUrl,
        string $description,
        ?string $context,
        ?array $objectives,
        BriefDatePeriod $period,
        DifficultyLevel $difficulty,
        BriefModality $modality,
        ?string $pedagogicalModalities,
        ?string $evaluationModalities,
        BriefStatus $status,
        int $points = 0,
        array $tags = [],
        array $resources = [],
        ?array $deliverables = [],
        ?array $performanceCriteria = [],
        ?array $targetCompetencies = [],
        int $formateurId,
        bool $hasQuiz = false
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->imageUrl = $imageUrl;
        $this->description = $description;
        $this->context = $context;
        $this->objectives = $objectives;
        $this->period = $period;
        $this->difficulty = $difficulty;
        $this->modality = $modality;
        $this->pedagogicalModalities = $pedagogicalModalities;
        $this->evaluationModalities = $evaluationModalities;
        $this->status = $status;
        $this->points = $points;
        $this->tags = $tags;
        $this->resources = $resources;
        $this->deliverables = $deliverables;
        $this->performanceCriteria = $performanceCriteria;
        $this->targetCompetencies = $targetCompetencies;
        $this->formateurId = $formateurId;
        $this->hasQuiz = $hasQuiz;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): BriefTitle { return $this->title; }
    public function getImageUrl(): ?string { return $this->imageUrl; }
    public function getDescription(): string { return $this->description; }
    public function getContext(): ?string { return $this->context; }
    public function getObjectives(): ?array { return $this->objectives; }
    public function getPeriod(): BriefDatePeriod { return $this->period; }
    public function getDifficulty(): DifficultyLevel { return $this->difficulty; }
    public function getModality(): BriefModality { return $this->modality; }
    public function getPedagogicalModalities(): ?string { return $this->pedagogicalModalities; }
    public function getEvaluationModalities(): ?string { return $this->evaluationModalities; }
    public function getStatus(): BriefStatus { return $this->status; }
    public function getPoints(): int { return $this->points; }
    public function getTags(): array { return $this->tags; }
    public function getResources(): array { return $this->resources; }
    public function getDeliverables(): ?array { return $this->deliverables; }
    public function getPerformanceCriteria(): ?array { return $this->performanceCriteria; }
    public function getTargetCompetencies(): ?array { return $this->targetCompetencies; }
    public function getFormateurId(): int { return $this->formateurId; }
    public function hasQuiz(): bool { return $this->hasQuiz; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title->getValue(),
            'image_url' => $this->imageUrl,
            'description' => $this->description,
            'context' => $this->context,
            'objectives' => $this->objectives,
            'date_start' => $this->period->getStartDateString(),
            'date_end' => $this->period->getEndDateString(),
            'difficulty' => $this->difficulty->getValue(),
            'modality' => $this->modality->getValue(),
            'pedagogical_modalities' => $this->pedagogicalModalities,
            'evaluation_modalities' => $this->evaluationModalities,
            'status' => $this->status->getValue(),
            'points' => $this->points,
            'tags' => $this->tags,
            'resources' => $this->resources,
            'deliverables' => $this->deliverables,
            'performance_criteria' => $this->performanceCriteria,
            'target_competencies' => $this->targetCompetencies,
            'formateur_id' => $this->formateurId,
            'has_quiz' => $this->hasQuiz
        ];
    }
}
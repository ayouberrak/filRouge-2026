<?php

namespace App\Modules\Brief\Application\DTO;

class BriefDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $image_url,
        public readonly string $description,
        public readonly ?string $context,
        public readonly ?array $objectives,
        public readonly string $date_start,
        public readonly string $date_end,
        public readonly string $difficulty,
        public readonly string $modality,
        public readonly ?string $pedagogical_modalities,
        public readonly ?string $evaluation_modalities,
        public readonly string $status,
        public readonly ?int $points = 0,
        public readonly ?array $tags = [],
        public readonly ?array $resources = [],
        public readonly ?array $deliverables = [],
        public readonly ?array $performance_criteria = [],
        public readonly ?array $target_competencies = [],
        public readonly ?string $file = null,
        public readonly ?int $formateur_id = null
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'context' => $this->context,
            'objectives' => $this->objectives,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'difficulty' => $this->difficulty,
            'modality' => $this->modality,
            'pedagogical_modalities' => $this->pedagogical_modalities,
            'evaluation_modalities' => $this->evaluation_modalities,
            'status' => $this->status,
            'points' => $this->points,
            'tags' => $this->tags,
            'resources' => $this->resources,
            'deliverables' => $this->deliverables,
            'performance_criteria' => $this->performance_criteria,
            'target_competencies' => $this->target_competencies,
            'file' => $this->file,
            'formateur_id' => $this->formateur_id
        ];
    }
}

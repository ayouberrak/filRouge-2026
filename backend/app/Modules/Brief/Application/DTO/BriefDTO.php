<?php

namespace App\Modules\Brief\Application\DTO;

class BriefDTO
{
    public function __construct(
        public string $title,
        public ?string $image_url,
        public string $description,
        public ?string $context,
        public string $date_start,
        public string $date_end,
        public string $modality,
        public string $status,
        public ?array $tags = [],
        public ?string $file = null,
        public ?int $formateur_id = null,
        public ?int $classroom_id = null,
        public ?array $squad_ids = []
    ) {}

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'context' => $this->context,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'modality' => $this->modality,
            'status' => $this->status,
            'tags' => $this->tags,
            'file' => $this->file,
            'formateur_id' => $this->formateur_id,
            'classroom_id' => $this->classroom_id,
            'squad_ids' => $this->squad_ids
        ];
    }
}

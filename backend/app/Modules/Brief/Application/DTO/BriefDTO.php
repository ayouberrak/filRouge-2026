<?php

namespace App\Modules\Brief\Application\DTO;

class BriefDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $image_url,
        public readonly string $description,
        public readonly ?string $context,
        public readonly string $date_start,
        public readonly string $date_end,
        public readonly string $modality,
        public readonly string $status,
        public readonly ?array $tags = [],
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
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'modality' => $this->modality,
            'status' => $this->status,
            'tags' => $this->tags,
            'file' => $this->file,
            'formateur_id' => $this->formateur_id
        ];
    }
}

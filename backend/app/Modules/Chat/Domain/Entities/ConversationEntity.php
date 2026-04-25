<?php

namespace App\Modules\Chat\Domain\Entities;

use App\Modules\Chat\Domain\ValueObjects\ConversationType;
class ConversationEntity
{
    private ?int $id;
    private ConversationType $type;
    private int $relatedId;
    private string $name;

    public function __construct(?int $id, ConversationType $type, int $relatedId, string $name)
    {
        $this->id = $id;
        $this->type = $type;
        $this->relatedId = $relatedId;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ConversationType
    {
        return $this->type;
    }

    public function getRelatedId(): int
    {
        return $this->relatedId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
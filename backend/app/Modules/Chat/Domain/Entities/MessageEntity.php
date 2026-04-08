<?php

namespace App\Modules\Chat\Domain\Entities;

use App\Modules\Chat\Domain\ValueObjects\MessageContent;
class MessageEntity
{
    private ?int $id;
    private int $conversationId;
    private int $senderId;
    private MessageContent $content;
    private array $readBy;

    public function __construct(?int $id, int $conversationId, int $senderId, MessageContent $content, array $readBy = [])
    {
        $this->id = $id;
        $this->conversationId = $conversationId;
        $this->senderId = $senderId;
        $this->content = $content;
        $this->readBy = $readBy;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversationId(): int
    {
        return $this->conversationId;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function getContent(): MessageContent
    {
        return $this->content;
    }

    public function getReadBy(): array
    {
        return $this->readBy;
    }

    public function markAsRead(int $userId): void
    {
        if (!in_array($userId, $this->readBy)) {
            $this->readBy[] = $userId;
        }
    }
}
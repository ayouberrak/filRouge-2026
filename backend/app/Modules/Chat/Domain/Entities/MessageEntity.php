<?php

namespace App\Modules\Chat\Domain\Entities;

use App\Modules\Chat\Domain\ValueObjects\MessageContent;
class MessageEntity
{
    private ?int $id;
    private int $conversationId;
    private int $senderId;
    private MessageContent $content;

    public function __construct(?int $id, int $conversationId, int $senderId, MessageContent $content)
    {
        $this->id = $id;
        $this->conversationId = $conversationId;
        $this->senderId = $senderId;
        $this->content = $content;
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
}
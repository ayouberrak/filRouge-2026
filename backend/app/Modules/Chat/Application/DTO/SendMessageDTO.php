<?php

namespace App\Modules\Chat\Application\DTO;

class SendMessageDTO
{
    public function __construct(
        public readonly int $conversationId,
        public readonly int $senderId,
        public readonly string $content,
        public readonly ?string $attachmentUrl = null
    ) {}

    public static function fromRequest(array $data, int $senderId): self
    {
        return new self(
            $data['conversation_id'],
            $senderId,
            $data['content'],
            $data['attachment_url'] ?? null
        );
    }
}

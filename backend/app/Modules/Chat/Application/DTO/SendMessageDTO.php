<?php

namespace App\Modules\Chat\Application\DTO;

class SendMessageDTO
{
    public function __construct(
        public int $conversationId,
        public int $senderId,
        public string $content
    ) {}

    public static function toDTOO(array $data, int $senderId): self
    {
        return new self(
            $data['conversation_id'],
            $senderId,
            $data['content']
        );
    }
}

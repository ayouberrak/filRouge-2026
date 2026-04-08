<?php

namespace App\Modules\Chat\Domain\Repositories;


interface ChatRepositoryInterface
{
    public function createConversation(string $type, ?int $relatedId = null, ?string $name = null);

    public function addUserToConversation(int $conversationId, int $userId);

    public function removeUserFromConversation(int $conversationId, int $userId);

    public function sendMessage(int $conversationId, int $senderId, string $content);

    public function markMessageAsRead(int $messageId, int $userId);

    public function getConversationMessages(int $conversationId);

    public function getUserConversations(int $userId);
}
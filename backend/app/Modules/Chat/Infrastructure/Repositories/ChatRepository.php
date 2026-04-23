<?php

namespace App\Modules\Chat\Infrastructure\Repositories;

use App\Modules\Chat\Infrastructure\Models\ConversationModel;
use App\Modules\Chat\Infrastructure\Models\MessageModel;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;

class ChatRepository implements ChatRepositoryInterface
{
    public function createConversation(string $type, ?int $relatedId = null, ?string $name = null): ConversationModel
    {
        return ConversationModel::create([
            'type' => $type,
            'related_id' => $relatedId,
            'name' => $name,
        ]);
    }

    public function addUserToConversation(int $conversationId, int $userId): void
    {
        $conversation = ConversationModel::findOrFail($conversationId);
        $conversation->users()->attach($userId, ['joined_at' => now()]);
    }

    public function removeUserFromConversation(int $conversationId, int $userId): void
    {
        $conversation = ConversationModel::findOrFail($conversationId);
        $conversation->users()->updateExistingPivot($userId, ['left_at' => now()]);
    }

    public function sendMessage(int $conversationId, int $senderId, string $content): MessageModel
    {
        return MessageModel::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'content' => $content,
        ]);
    }

    public function markMessageAsRead(int $messageId, int $userId): void
    {
        // Method kept for interface compatibility but logic removed as per user request
    }

    public function getConversationMessages(int $conversationId): Collection
    {
        return MessageModel::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getUserConversations(int $userId): Collection
    {
        return ConversationModel::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->whereNull('conversation_user.left_at');
            })
            ->with(['users', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}

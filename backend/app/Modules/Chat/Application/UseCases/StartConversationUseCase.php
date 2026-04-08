<?php

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Infrastructure\Models\ConversationModel;
use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;

class StartConversationUseCase
{
    private ChatRepositoryInterface $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    /**
     * Start a private conversation between two users.
     * If one already exists, return it.
     */
    public function execute(int $currentUserId, int $targetUserId): ConversationModel
    {
        // Try to find an existing individual conversation between these two
        $existing = ConversationModel::where('type', 'individual')
            ->whereHas('users', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })
            ->whereHas('users', function ($q) use ($targetUserId) {
                $q->where('user_id', $targetUserId);
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        // Create new
        $conversation = $this->chatRepository->createConversation('individual');
        $this->chatRepository->addUserToConversation($conversation->id, $currentUserId);
        $this->chatRepository->addUserToConversation($conversation->id, $targetUserId);

        return $conversation->load('users');
    }
}

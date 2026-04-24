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

    public function execute(int $currentUserId, int $targetUserId, string $type = 'individual', ?string $name = null): ConversationModel
    {
        if ($type === 'individual') {
            // Find an individual conversation where BOTH users are present using a direct join
            $existingId = \Illuminate\Support\Facades\DB::table('conversation_user as cu1')
                ->join('conversation_user as cu2', 'cu1.conversation_id', '=', 'cu2.conversation_id')
                ->join('conversations as c', 'c.id', '=', 'cu1.conversation_id')
                ->where('c.type', 'individual')
                ->where('cu1.user_id', $currentUserId)
                ->where('cu2.user_id', $targetUserId)
                ->value('c.id');

            if ($existingId) {
                return ConversationModel::find($existingId)->load('users');
            }
        }

        // Create new
        $conversation = $this->chatRepository->createConversation($type, null, $name);
        $this->chatRepository->addUserToConversation($conversation->id, $currentUserId);
        
        if ($targetUserId && $currentUserId !== $targetUserId) {
            $this->chatRepository->addUserToConversation($conversation->id, $targetUserId);
        }

        // Touch the conversation to ensure it shows up at the top
        $conversation->touch();

        return $conversation->load('users');
    }
}

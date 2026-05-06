<?php

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Infrastructure\Models\ConversationModel;
use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;
use DB;

class StartConversationUseCase
{
    private ChatRepositoryInterface $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function execute(int $currentUserId, int $targetId, string $type = 'individual', ?string $name = null): ConversationModel
    {
        // 1. Pour les chats individuels
        if ($type === 'individual') {
            $existingId = DB::table('conversation_user as cu1')
                ->join('conversation_user as cu2', 'cu1.conversation_id', '=', 'cu2.conversation_id')
                ->join('conversations as c', 'c.id', '=', 'cu1.conversation_id')
                ->where('c.type', 'individual')
                ->where('cu1.user_id', $currentUserId)
                ->where('cu2.user_id', $targetId)
                ->value('c.id');

            if ($existingId) {
                $conversation = ConversationModel::find($existingId);
                $conversation->load('users');
                return $conversation;
            }
        } 
        // 2. Pour les chats de groupe (classroom / squad)
        else {
            $existingId = ConversationModel::where('type', $type)
                ->where('related_id', $targetId)
                ->value('id');

            if ($existingId) {
                $conversation = ConversationModel::find($existingId);
                
                // On s'assure que l'utilisateur est bien dans la conversation
                $exists = DB::table('conversation_user')
                    ->where('conversation_id', $existingId)
                    ->where('user_id', $currentUserId)
                    ->exists();
                
                if (!$exists) {
                    $this->chatRepository->addUserToConversation($existingId, $currentUserId);
                }

                return $conversation->load('users');
            }
        }

        // 3. Création d'une nouvelle conversation si elle n'existe pas
        $relatedId = ($type !== 'individual') ? $targetId : null;
        $conversation = $this->chatRepository->createConversation($type, $relatedId, $name);
        $this->chatRepository->addUserToConversation($conversation->id, $currentUserId);
        
        if ($type === 'individual' && $targetId && $currentUserId !== $targetId) {
            $this->chatRepository->addUserToConversation($conversation->id, $targetId);
        }

        $conversation->touch();
        return $conversation->load('users');
    }
}

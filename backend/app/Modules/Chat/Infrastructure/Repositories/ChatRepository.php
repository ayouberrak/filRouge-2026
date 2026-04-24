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
        $message = MessageModel::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'content' => $content,
        ]);

        ConversationModel::where('id', $conversationId)->update(['updated_at' => now()]);

        return $message;
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
        $user = \App\Modules\User\Infrastructure\Models\UserModel::find($userId);

        // Ensure user is in classroom conversation
        if ($user && $user->classroom_id) {
            $classConv = ConversationModel::where('type', 'classroom')->where('related_id', $user->classroom_id)->first();
            if ($classConv) {
                $exists = \Illuminate\Support\Facades\DB::table('conversation_user')
                    ->where('conversation_id', $classConv->id)
                    ->where('user_id', $userId)
                    ->exists();
                if (!$exists) {
                    $this->addUserToConversation($classConv->id, $userId);
                }
            }
        }

        // Ensure user is in squad conversation
        if ($user && $user->squad_id) {
            $squadConv = ConversationModel::where('type', 'squad')->where('related_id', $user->squad_id)->first();
            if ($squadConv) {
                $exists = \Illuminate\Support\Facades\DB::table('conversation_user')
                    ->where('conversation_id', $squadConv->id)
                    ->where('user_id', $userId)
                    ->exists();
                if (!$exists) {
                    $this->addUserToConversation($squadConv->id, $userId);
                }
            }
        }

        // For formateurs: Ensure they are in all their managed classrooms' conversations
        if ($user && $user->role === 'formateur') {
            $managedClassrooms = \App\Modules\Classroom\Infrastructure\Models\ClassroomModel::where('formateur_id', $userId)->get();
            foreach ($managedClassrooms as $mClass) {
                // Classroom Chat
                $classConv = ConversationModel::where('type', 'classroom')->where('related_id', $mClass->id)->first();
                if ($classConv) {
                    $exists = \Illuminate\Support\Facades\DB::table('conversation_user')
                        ->where('conversation_id', $classConv->id)
                        ->where('user_id', $userId)
                        ->exists();
                    if (!$exists) {
                        $this->addUserToConversation($classConv->id, $userId);
                    }
                }

                // Squad Chats in this classroom
                $squads = \App\Modules\Squad\Infrastructure\Models\SquadModel::where('classroom_id', $mClass->id)->get();
                foreach ($squads as $squad) {
                    $squadConv = ConversationModel::where('type', 'squad')->where('related_id', $squad->id)->first();
                    if ($squadConv) {
                        $exists = \Illuminate\Support\Facades\DB::table('conversation_user')
                            ->where('conversation_id', $squadConv->id)
                            ->where('user_id', $userId)
                            ->exists();
                        if (!$exists) {
                            $this->addUserToConversation($squadConv->id, $userId);
                        }
                    }
                }
            }
        }

        $conversations = ConversationModel::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                ->whereNull('conversation_user.left_at');
        })
            ->with([
                'users',
                'messages' => function ($query) {
                    $query->latest()->limit(1);
                }
            ])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Append last_message for frontend compatibility
        $conversations->each(function ($conv) {
            $conv->last_message = $conv->messages->first();
        });

        return $conversations;
    }
}

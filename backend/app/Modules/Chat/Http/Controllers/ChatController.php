<?php

namespace App\Modules\Chat\Http\Controllers;

use App\Modules\Chat\Application\DTO\SendMessageDTO;
use App\Modules\Chat\Application\UseCases\GetConversationsUseCase;
use App\Modules\Chat\Application\UseCases\GetMessagesUseCase;
use App\Modules\Chat\Application\UseCases\SendMessageUseCase;
use App\Modules\Chat\Application\UseCases\SearchUsersUseCase;
use App\Modules\Chat\Application\UseCases\StartConversationUseCase;
use App\Modules\Chat\Http\Requests\SendMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Modules\Chat\Events\MessageSent;

class ChatController
{
    private GetConversationsUseCase $getConversations;
    private GetMessagesUseCase $getMessages;
    private SendMessageUseCase $sendMessage;
    private SearchUsersUseCase $searchUsers;
    private StartConversationUseCase $startConversation;

    public function __construct(
        GetConversationsUseCase $getConversations,
        GetMessagesUseCase $getMessages,
        SendMessageUseCase $sendMessage,
        SearchUsersUseCase $searchUsers,
        StartConversationUseCase $startConversation
    ) {
        $this->getConversations = $getConversations;
        $this->getMessages = $getMessages;
        $this->sendMessage = $sendMessage;
        $this->searchUsers = $searchUsers;
        $this->startConversation = $startConversation;
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $conversations = $this->getConversations->execute($userId);
        return response()->json($conversations);
    }

    public function show(int $conversationId): JsonResponse
    {
        $messages = $this->getMessages->execute($conversationId);
        return response()->json($messages);
    }

    public function store(SendMessageRequest $request): JsonResponse
    {
        $dto = SendMessageDTO::toDTOO($request->validated(), $request->user()->id);
        $message = $this->sendMessage->execute($dto);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        $users = $this->searchUsers->execute($query, $request->user()->id);
        return response()->json($users);
    }

    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $conversation = $this->startConversation->execute( $request->user()->id, $request->input('user_id')
        );

        return response()->json($conversation);
    }
}

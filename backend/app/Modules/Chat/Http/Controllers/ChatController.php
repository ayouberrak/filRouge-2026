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
        $user = $request->user();

        $users = $this->searchUsers->execute($query, $user->id);
        
        $results = [];
        foreach ($users as $u) {
            $results[] = [
                'id' => $u->id,
                'name' => $u->first_name . ' ' . $u->last_name,
                'role' => $u->role,
                'type' => 'individual'
            ];
        }

        // Si formateur, on ajoute ses classes et squads
        if ($user->role === 'formateur') {
            $classrooms = \App\Modules\Classroom\Infrastructure\Models\ClassroomModel::where('formateur_id', $user->id)
                ->where('name', 'LIKE', "%{$query}%")
                ->get();
            
            foreach ($classrooms as $class) {
                $results[] = [
                    'id' => $class->id, // Here ID refers to the related_id in conversation
                    'name' => $class->name,
                    'role' => 'Classe',
                    'type' => 'classroom'
                ];

                $squads = \App\Modules\Squad\Infrastructure\Models\SquadModel::where('classroom_id', $class->id)
                    ->where('name', 'LIKE', "%{$query}%")
                    ->get();
                
                foreach ($squads as $squad) {
                    $results[] = [
                        'id' => $squad->id,
                        'name' => $squad->name,
                        'role' => 'Squad',
                        'type' => 'squad'
                    ];
                }
            }
        }

        return response()->json($results);
    }

    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'sometimes|string|in:individual,squad,classroom',
            'name' => 'sometimes|string|nullable'
        ]);

        $conversation = $this->startConversation->execute(
            $request->user()->id,
            $request->input('user_id'),
            $request->input('type', 'individual'),
            $request->input('name')
        );

        return response()->json($conversation);
    }
}

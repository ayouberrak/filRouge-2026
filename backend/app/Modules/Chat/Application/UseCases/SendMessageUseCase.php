<?php

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Application\DTO\SendMessageDTO;
use App\Modules\Chat\Domain\ValueObjects\MessageContent;
use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;

class SendMessageUseCase
{
    private ChatRepositoryInterface $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function execute(SendMessageDTO $dto)
    {
        // 1. Validation métier via Value Object
        $content = new MessageContent($dto->content);

        // 2. Sauvegarde en DB via le repository
        $message = $this->chatRepository->sendMessage(
            $dto->conversationId,
            $dto->senderId,
            $content->getContent()
        );

        // 3. Mettre à jour la date de modification de la conversation
        // $message->conversation->touch(); // Assurez-vous d'avoir la relation dans le modèle
        
        return $message;
    }
}

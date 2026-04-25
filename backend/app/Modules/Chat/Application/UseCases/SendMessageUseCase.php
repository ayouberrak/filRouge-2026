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
        $content = new MessageContent($dto->content);

        $message = $this->chatRepository->sendMessage( $dto->conversationId , $dto->senderId , $content->getContent()
        );        
        return $message;
    }
}

<?php

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetMessagesUseCase
{
    private ChatRepositoryInterface $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function execute(int $conversationId): Collection
    {
        return $this->chatRepository->getConversationMessages($conversationId);
    }
}

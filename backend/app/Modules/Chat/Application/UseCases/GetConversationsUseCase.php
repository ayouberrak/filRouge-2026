<?php

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\Chat\Domain\Repositories\ChatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetConversationsUseCase
{
    private ChatRepositoryInterface $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    public function execute(int $userId): Collection
    {
        return $this->chatRepository->getUserConversations($userId);
    }
}

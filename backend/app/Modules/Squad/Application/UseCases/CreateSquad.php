<?php

namespace App\Modules\Squad\Application\UseCases;

use App\Modules\Squad\Application\DTO\CreateSquadDTO;
use App\Modules\Squad\Domain\Repositories\SquadRepositoryInterface;
use App\Modules\Squad\Domain\Entities\SquadEntity;

class CreateSquad
{
    public function __construct(
        private SquadRepositoryInterface $squadRepository
    ) {}

    public function execute(CreateSquadDTO $dto): ?SquadEntity
    {
        $squadData = [
            'name' => $dto->name,
            'classroom_id' => $dto->classroom_id
        ];

        $squad = $this->squadRepository->create($squadData);

        if ($squad && !empty($dto->members)) {
            foreach ($dto->members as $memberId) {
                // Call entity behavioral method conceptually, then persist
                $squad->addMember($memberId);
                $this->squadRepository->assignMember($squad->getId(), $memberId);
            }
        }

        // Create Chat Conversation for the squad
        if ($squad) {
            \App\Modules\Chat\Infrastructure\Models\ConversationModel::firstOrCreate(
                ['type' => 'squad', 'related_id' => $squad->getId()],
                ['name' => 'Squad: ' . $squad->getName()->getValue()]
            );
        }

        // Return a fresh copy with members loaded
        return $squad ? $this->squadRepository->findById($squad->getId()) : null;
    }
}

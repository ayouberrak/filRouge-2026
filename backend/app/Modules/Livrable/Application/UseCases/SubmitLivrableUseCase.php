<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Livrable\Application\DTO\SubmitLivrableDTO;
use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;
use InvalidArgumentException;

class SubmitLivrableUseCase
{
    private LivrableRepositoryInterface $repository;

    public function __construct(LivrableRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(SubmitLivrableDTO $dto): LivrableEntity
    {
        if (!$dto->studentId && !$dto->squadId) {
            throw new InvalidArgumentException("Un Livrable doit être soumis par un étudiant ou un squad.");
        }

        $livrable = new LivrableEntity(
            null,
            $dto->briefId,
            $dto->studentId,
            $dto->squadId,
            $dto->link,
            new LivrableStatus(LivrableStatus::SUBMITTED),
            [],
            null,
            $dto->message
        );

        return $this->repository->save($livrable);
    }
}

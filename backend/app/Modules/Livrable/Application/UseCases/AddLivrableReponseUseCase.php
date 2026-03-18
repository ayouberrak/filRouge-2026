<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO;
use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Entities\ReponseLivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;
use InvalidArgumentException;

class AddLivrableReponseUseCase
{
    private LivrableRepositoryInterface $repository;

    public function __construct(LivrableRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(AddLivrableReponseDTO $dto): ReponseLivrableEntity
    {
        $livrable = $this->repository->findById($dto->livrableId);

        if (!$livrable) {
            throw new InvalidArgumentException("Livrable non trouvé.");
        }

        if (!in_array($dto->status, ['validé', 'invalidé'])) {
            throw new InvalidArgumentException("Le statut de réponse doit être 'validé' ou 'invalidé'.");
        }

        $status = new LivrableStatus($dto->status);

        $reponse = new ReponseLivrableEntity(
            null,
            $dto->livrableId,
            $dto->formateurId,
            $status,
            $dto->message
        );

        return $this->repository->saveResponse($reponse);
    }
}

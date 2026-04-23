<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO;
use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;
use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AddLivrableReponseUseCase
{
    public function __construct(
        private LivrableRepositoryInterface $repository,
        private BriefRepositoryInterface $briefRepository
    ) {}

    public function execute(AddLivrableReponseDTO $dto): LivrableEntity
    {
        return DB::transaction(function () use ($dto) {
            $livrable = $this->repository->findById($dto->livrableId);
            if (!$livrable) {
                throw new InvalidArgumentException("Livrable non trouvé.");
            }

            if (!in_array(strtoupper($dto->status), ['VALIDATED', 'REJECTED'])) {
                throw new InvalidArgumentException("Le statut de réponse doit être 'VALIDATED' ou 'REJECTED'.");
            }

            $livrable->setStatus(new LivrableStatus($dto->status));
            $livrable->setFormateurId($dto->formateurId);
            $livrable->setFormateurMessage($dto->message);

            return $this->repository->save($livrable);
        });
    }
}

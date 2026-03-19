<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Livrable\Application\DTO\AddLivrableReponseDTO;
use App\Modules\Livrable\Domain\Entities\ReponseLivrableEntity;
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

    public function execute(AddLivrableReponseDTO $dto): ReponseLivrableEntity
    {
        return DB::transaction(function () use ($dto) {
            $livrable = $this->repository->findById($dto->livrableId);
            if (!$livrable) {
                throw new InvalidArgumentException("Livrable non trouvé.");
            }

            if (!in_array(strtoupper($dto->status), ['VALIDATED', 'REJECTED'])) {
                throw new InvalidArgumentException("Le statut de réponse doit être 'VALIDATED' ou 'REJECTED'.");
            }

            $status = new LivrableStatus($dto->status);

            $reponse = new ReponseLivrableEntity(
                null,
                $dto->livrableId,
                $dto->formateurId,
                $status,
                $dto->message
            );

            $savedReponse = $this->repository->saveResponse($reponse);

            // Reward Points if Validated
            if (strtoupper($dto->status) === 'VALIDATED') {
                $brief = $this->briefRepository->findById($livrable->getBriefId());
                if ($brief) {
                    $points = $brief->getPoints();
                    $user = UserModel::findOrFail($livrable->getStudentId());
                    $user->total_points += $points;
                    $user->save();
                }
            }

            return $savedReponse;
        });
    }
}

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
        private BriefRepositoryInterface $briefRepository,
        private \App\Modules\Brief\Application\UseCases\AwardPointsForBriefCompletionUseCase $awardPointsUseCase
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

            // Try to award points (only if both project and quiz are done)
            if (strtoupper($dto->status) === 'VALIDATED') {
                try {
                    $this->awardPointsUseCase->execute($livrable->getBriefId(), $livrable->getStudentId());
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to award points during project validation: " . $e->getMessage());
                }
            }

            return $savedReponse;
        });
    }
}

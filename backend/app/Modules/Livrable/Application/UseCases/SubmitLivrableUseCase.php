<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\User\Infrastructure\Models\UserModel;
use App\Modules\Livrable\Application\DTO\SubmitLivrableDTO;
use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
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
        $studentId = $dto->studentId;
        $squadId = $dto->squadId;

        if ($studentId && !$squadId) {
            $brief = BriefModel::find($dto->briefId);
            if ($brief && $brief->modality === 'GROUP') {
                $student = UserModel::find($studentId);
                if ($student && $student->squad_id) {
                    $squadId = $student->squad_id;
                }
            }
        }

        if (!$studentId && !$squadId) {
            throw new InvalidArgumentException("Un Livrable doit être soumis par un étudiant ou un squad.");
        }
        $existing = LivrableModel::where('brief_id', $dto->briefId)
            ->where(function($query) use ($studentId, $squadId) {
                if ($squadId) {
                    $query->where('squad_id', $squadId);
                } else {
                    $query->where('student_id', $studentId);
                }
            })
            ->first();

        $livrable = new LivrableEntity(
            id: $existing ? $existing->id : null,
            briefId: $dto->briefId,
            studentId: $studentId,
            squadId: $squadId,
            link: $dto->link,
            status: new LivrableStatus(LivrableStatus::SUBMITTED),
            message: $dto->message
        );

        return $this->repository->save($livrable);
    }
}

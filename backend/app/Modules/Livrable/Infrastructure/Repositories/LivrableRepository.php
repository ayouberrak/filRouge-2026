<?php

namespace App\Modules\Livrable\Infrastructure\Repositories;

use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;

class LivrableRepository implements LivrableRepositoryInterface
{
    public function save(LivrableEntity $livrable): LivrableEntity
    {
        $livrableModel = LivrableModel::updateOrCreate(
            ['id' => $livrable->getId()],
            [
                'brief_id' => $livrable->getBriefId(),
                'student_id' => $livrable->getStudentId(),
                'squad_id' => $livrable->getSquadId(),
                'link' => $livrable->getLink(),
                'message' => $livrable->getMessage(),
                'status' => $livrable->getStatus()->getValue(),
                'formateur_id' => $livrable->getFormateurId(),
                'formateur_message' => $livrable->getFormateurMessage(),
            ]
        );

        return $this->toEntity($livrableModel);
    }

    public function findById(int $id): ?LivrableEntity
    {
        $model = LivrableModel::find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByBriefId(int $briefId): array
    {
        $models = LivrableModel::where('brief_id', $briefId)->get();
        return $models->map(function(LivrableModel $model) {
            return $this->toEntity($model);
        })->toArray();
    }

    private function toEntity(LivrableModel $model): LivrableEntity
    {
        return new LivrableEntity(
            $model->id,
            $model->brief_id,
            $model->student_id,
            $model->squad_id,
            $model->link,
            new LivrableStatus($model->status),
            $model->formateur_id,
            $model->formateur_message,
            $model->updated_at,
            $model->message,
            $model->created_at
        );
    }
}

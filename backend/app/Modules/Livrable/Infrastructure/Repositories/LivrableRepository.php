<?php

namespace App\Modules\Livrable\Infrastructure\Repositories;

use App\Modules\Livrable\Domain\Entities\LivrableEntity;
use App\Modules\Livrable\Domain\Entities\ReponseLivrableEntity;
use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Domain\ValueObjects\LivrableStatus;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;
use App\Modules\Livrable\Infrastructure\Models\ReponseLivrableModel;

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
                'status' => $livrable->getStatus()->getValue(),
            ]
        );

        return $this->toEntity($livrableModel);
    }

    public function findById(int $id): ?LivrableEntity
    {
        $model = LivrableModel::with('responses')->find($id);

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function saveResponse(ReponseLivrableEntity $reponse): ReponseLivrableEntity
    {
        $reponseModel = ReponseLivrableModel::updateOrCreate(
            ['id' => $reponse->getId()],
            [
                'livrable_id' => $reponse->getLivrableId(),
                'formateur_id' => $reponse->getFormateurId(),
                'status' => $reponse->getStatus()->getValue(),
                'message' => $reponse->getMessage(),
            ]
        );

        // Update the livrable status
        LivrableModel::where('id', $reponse->getLivrableId())
            ->update(['status' => $reponse->getStatus()->getValue()]);

        return $this->toResponseEntity($reponseModel);
    }

    public function findResponseById(int $id): ?ReponseLivrableEntity
    {
        $model = ReponseLivrableModel::find($id);
        return $model ? $this->toResponseEntity($model) : null;
    }

    private function toEntity(LivrableModel $model): LivrableEntity
    {
        $responses = $model->relationLoaded('responses')
            ? $model->responses->map(fn($resp) => $this->toResponseEntity($resp))->toArray()
            : [];

        return new LivrableEntity(
            $model->id,
            $model->brief_id,
            $model->student_id,
            $model->squad_id,
            $model->link,
            new LivrableStatus($model->status),
            $responses
        );
    }

    private function toResponseEntity(ReponseLivrableModel $model): ReponseLivrableEntity
    {
        return new ReponseLivrableEntity(
            $model->id,
            $model->livrable_id,
            $model->formateur_id,
            new LivrableStatus($model->status),
            $model->message
        );
    }
}

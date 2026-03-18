<?php

namespace App\Modules\Brief\Infrastructure\Repositories;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Brief\Domain\Entities\BriefEntity;
use App\Modules\Brief\Domain\ValueObjects\BriefTitle;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\DifficultyLevel;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;

class BriefRepository implements BriefRepositoryInterface
{
    public function save(BriefEntity $brief): BriefEntity
    {
        $data = [
            'title' => $brief->getTitle()->getValue(),
            'description' => $brief->getDescription(),
            'objectives' => $brief->getObjectives(),
            'date_start' => $brief->getPeriod()->getStartDateString(),
            'date_end' => $brief->getPeriod()->getEndDateString(),
            'difficulty' => $brief->getDifficulty()->getValue(),
            'modality' => $brief->getModality()->getValue(),
            'status' => $brief->getStatus()->getValue(),
            'tags' => $brief->getTags(),
            'resources' => $brief->getResources(),
            'formateur_id' => $brief->getFormateurId(),
        ];

        if ($brief->getId()) {
            /** @var BriefModel $model */
            $model = BriefModel::find($brief->getId());
            if ($model) {
                $model->update($data);
            } else {
                /** @var BriefModel $model */
                $model = BriefModel::create($data);
            }
        } else {
            /** @var BriefModel $model */
            $model = BriefModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function findById(int $id): ?BriefEntity
    {
        /** @var BriefModel|null $model */
        $model = BriefModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByClassroomId(int $classroomId): array
    {
        $models = BriefModel::whereHas('classrooms', function($query) use ($classroomId) {
            $query->where('classroom_id', $classroomId);
        })->get();
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }
    
    public function findByFormateurId(int $formateurId): array
    {
        $models = BriefModel::where('formateur_id', $formateurId)->get();
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }

    public function findAll(): array
    {
        $models = BriefModel::all();
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }

    public function delete(int $id): bool
    {
        /** @var BriefModel|null $model */
        $model = BriefModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function assignClassrooms(int $briefId, array $classroomIds): void
    {
        /** @var BriefModel|null $model */
        $model = BriefModel::find($briefId);
        if ($model) {
            $model->classrooms()->syncWithoutDetaching($classroomIds);
        }
    }

    private function toEntity(BriefModel $model): BriefEntity
    {
        return new BriefEntity(
            $model->id,
            new BriefTitle($model->title),
            $model->description,
            $model->objectives,
            new BriefDatePeriod($model->date_start->format('Y-m-d H:i:s'), $model->date_end->format('Y-m-d H:i:s')),
            new DifficultyLevel($model->difficulty),
            new BriefModality($model->modality),
            new BriefStatus($model->status),
            $model->tags ?? [],
            $model->resources ?? [],
            $model->formateur_id
        );
    }
}

<?php

namespace App\Modules\Brief\Infrastructure\Repositories;

use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Infrastructure\Models\BriefModel;
use App\Modules\Brief\Domain\Entities\BriefEntity;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;

class BriefRepository implements BriefRepositoryInterface
{
    public function save(BriefEntity $brief): BriefEntity
    {
        $data = [
            'title' => $brief->getTitle(),
            'image_url' => $brief->getImageUrl(),
            'description' => $brief->getDescription(),
            'context' => $brief->getContext(),
            'date_start' => $brief->getPeriod()->getStartDateString(),
            'date_end' => $brief->getPeriod()->getEndDateString(),
            'modality' => $brief->getModality()->getValue(),
            'status' => $brief->getStatus()->getValue(),
            'tags' => $brief->getTags(),
            'formateur_id' => $brief->getFormateurId(),
        ];

        if ($brief->getId()) {
            $model = BriefModel::find($brief->getId());
            $model->update($data);
        } else {
            $model = BriefModel::create($data);
        }

        return $this->toEntity($model);
    }

    public function findById(int $id): ?BriefEntity
    {
        $model = BriefModel::withCount('quizSessions')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByClassroomId(int $classroomId, ?int $squadId = null): array
    {
        $models = BriefModel::withCount('quizSessions')
            ->where(function($query) use ($classroomId, $squadId) {
                $query->whereHas('classrooms', function($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                });
                
                if ($squadId) {
                    $query->orWhereHas('squads', function($q) use ($squadId) {
                        $q->where('squad_id', $squadId);
                    });
                }
            })->get();
            
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }
    public function findByClassroomIds(array $classroomIds): array
    {
        $models = BriefModel::withCount('quizSessions')
            ->where(function($query) use ($classroomIds) {
                // Assigné directement à la classe
                $query->whereHas('classrooms', function($q) use ($classroomIds) {
                    $q->whereIn('classroom_id', $classroomIds);
                })
                // OU assigné à une squad qui appartient à l'une de ces classes
                ->orWhereHas('squads', function($q) use ($classroomIds) {
                    $q->whereIn('classroom_id', $classroomIds);
                });
            })->get();
            
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }
    
    public function findByFormateurId(int $formateurId): array
    {
        $models = BriefModel::withCount('quizSessions')
            ->where('formateur_id', $formateurId)->get();
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }

    public function findAll(): array
    {
        $models = BriefModel::withCount('quizSessions')->get();
        return $models->map(fn(BriefModel $model) => $this->toEntity($model))->toArray();
    }

    public function delete(int $id): bool
    {
        $model = BriefModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function assignClassrooms(int $briefId, array $classroomIds): void
    {
        $model = BriefModel::find($briefId);
        if ($model) {
            $model->classrooms()->sync($classroomIds);

            $model->update([
                'status' => 'PUBLISHED'
            ]);
        }
    }

    public function assignSquads(int $briefId, array $squadIds): void
    {
        $model = BriefModel::find($briefId);
        if ($model) {
            $model->squads()->sync($squadIds);

            $model->update([
                'status' => 'PUBLISHED'
            ]);
        }
    }


    private function toEntity(BriefModel $model): BriefEntity
    {
        return new BriefEntity(
            $model->id,
            $model->title,
            $model->image_url,
            $model->description,
            $model->context,
            new BriefDatePeriod($model->date_start->format('Y-m-d H:i:s'), $model->date_end->format('Y-m-d H:i:s')),
            new BriefModality($model->modality),
            new BriefStatus($model->status),
            $model->tags ?? [],
            $model->formateur_id,
            $model->quiz_sessions_count > 0
        );
    }
}

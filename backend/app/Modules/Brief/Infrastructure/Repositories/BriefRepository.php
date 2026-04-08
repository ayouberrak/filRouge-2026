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
            'image_url' => $brief->getImageUrl(),
            'description' => $brief->getDescription(),
            'context' => $brief->getContext(),
            'objectives' => $brief->getObjectives(),
            'date_start' => $brief->getPeriod()->getStartDateString(),
            'date_end' => $brief->getPeriod()->getEndDateString(),
            'difficulty' => $brief->getDifficulty()->getValue(),
            'modality' => $brief->getModality()->getValue(),
            'pedagogical_modalities' => $brief->getPedagogicalModalities(),
            'evaluation_modalities' => $brief->getEvaluationModalities(),
            'status' => $brief->getStatus()->getValue(),
            'points' => $brief->getPoints(),
            'tags' => $brief->getTags(),
            'resources' => $brief->getResources(),
            'deliverables' => $brief->getDeliverables(),
            'performance_criteria' => $brief->getPerformanceCriteria(),
            'target_competencies' => $brief->getTargetCompetencies(),
            'formateur_id' => $brief->getFormateurId(),
        ];

        if ($brief->getId()) {
            /** @var BriefModel|null $model */
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
        $model = BriefModel::withCount('quizSessions')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByClassroomId(int $classroomId): array
    {
        $models = BriefModel::withCount('quizSessions')
            ->whereHas('classrooms', function($query) use ($classroomId) {
                $query->where('classroom_id', $classroomId);
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
        /** @var BriefModel|null $model */
        $model = BriefModel::find($id);
        return $model ? $model->delete() : false;
    }

    public function assignClassrooms(int $briefId, array $classroomIds): void
    {
        /** @var BriefModel|null $model */
        $model = BriefModel::find($briefId);
        if ($model) {
            // Attach classrooms
            $model->classrooms()->syncWithoutDetaching($classroomIds);
            // Auto-publish the brief upon assignment
            $model->update(['status' => 'PUBLISHED']);
        }
    }


    private function toEntity(BriefModel $model): BriefEntity
    {
        return new BriefEntity(
            $model->id,
            new BriefTitle($model->title),
            $model->image_url,
            $model->description,
            $model->context,
            $model->objectives,
            new BriefDatePeriod($model->date_start->format('Y-m-d H:i:s'), $model->date_end->format('Y-m-d H:i:s')),
            new DifficultyLevel($model->difficulty),
            new BriefModality($model->modality),
            $model->pedagogical_modalities,
            $model->evaluation_modalities,
            new BriefStatus($model->status),
            $model->points ?? 0,
            $model->tags ?? [],
            $model->resources ?? [],
            $model->deliverables ?? [],
            $model->performance_criteria ?? [],
            $model->target_competencies ?? [],
            $model->formateur_id,
            $model->quiz_sessions_count > 0
        );
    }
}

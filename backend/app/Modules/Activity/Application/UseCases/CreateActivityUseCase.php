<?php

namespace App\Modules\Activity\Application\UseCases;

use App\Modules\Activity\Domain\Entities\ActivityEntity;
use App\Modules\Activity\Domain\Repositories\ActivityRepositoryInterface;
use App\Modules\Activity\Domain\ValueObjects\ActivityType;
use App\Modules\Activity\Application\DTO\CreateActivityDTO;
use App\Modules\Activity\Domain\Events\ActivityAssignedToStudents;

class CreateActivityUseCase
{
    private ActivityRepositoryInterface $repository;

    public function __construct(ActivityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(CreateActivityDTO $dto): ActivityEntity
    {
        $activity = new ActivityEntity(
            null,
            $dto->title,
            $dto->description,
            new ActivityType($dto->type),
            $dto->duration,
            $dto->points,
            $dto->formateurId,
            $dto->classroomId
        );

        $savedActivity = $this->repository->save($activity);

        if (!empty($dto->studentIds)) {
            $this->repository->assignToStudents($savedActivity->getId(), $dto->studentIds);
            
            // Dispatch notification event
            ActivityAssignedToStudents::dispatch($savedActivity->getId(), $dto->studentIds);
        }

        return $savedActivity;
    }
}

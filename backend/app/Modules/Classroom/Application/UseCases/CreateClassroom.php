<?php

namespace App\Modules\Classroom\Application\UseCases;

use App\Modules\Classroom\Application\DTO\CreateClassroomDTO;
use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;

class CreateClassroom
{
    public function __construct(
        private ClassroomRepositoryInterface $classroomRepository
    ) {}

    public function execute(CreateClassroomDTO $classroomDTO): void
    {
        $this->classroomRepository->create([
            'name' => $classroomDTO->name,
            'formateur_id' => $classroomDTO->formateur_id
        ]);
    }
}
<?php

namespace App\Modules\Classroom\Application\UseCases;
use App\Modules\Classroom\Application\DTO\CreateClassroomDTO;
use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;


class UpdateClassroom
{
    public function __construct(
        private ClassroomRepositoryInterface $classroomRepository
    ) {}
 
    public function execute(int $classroomId, CreateClassroomDTO $classroomDTO): void
    {
        $this->classroomRepository->update($classroomId, $classroomDTO);
    }
}


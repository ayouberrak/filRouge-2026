<?php

namespace App\Modules\Classroom\Application\UseCases;

use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;

class GetClassroom
{
    public function __construct(
        private ClassroomRepositoryInterface $classroomRepository
    ) {}
 
    public function execute(int $classroomId)
    {
        return $this->classroomRepository->findById($classroomId);
    }
}
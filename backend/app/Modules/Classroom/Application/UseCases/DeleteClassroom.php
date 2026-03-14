<?php

namespace App\Modules\Classroom\Application\UseCases;

use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;

class DeleteClassroom
{
    public function __construct(
        private ClassroomRepositoryInterface $classroomRepository
    ) {}
 
    public function execute(int $classroomId): void
    {
        $this->classroomRepository->delete($classroomId);
    }
}
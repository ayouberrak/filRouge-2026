<?php

namespace App\Modules\Classroom\Application\UseCases;

use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;

class GetAllClassrooms
{
    public function __construct(
        private ClassroomRepositoryInterface $classroomRepository
    ) {}
 
    public function execute()
    {
        return $this->classroomRepository->findAll();
    }
}
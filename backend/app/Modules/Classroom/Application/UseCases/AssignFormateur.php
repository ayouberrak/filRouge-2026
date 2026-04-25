<?php

namespace App\Modules\Classroom\Application\UseCases;

use App\Modules\Classroom\Application\DTO\AssignFormateurDTO;
use App\Modules\Classroom\Domain\Repositories\ClassroomRepositoryInterface;

class AssignFormateur
{
    public function __construct(
        private ClassroomRepositoryInterface $classroomRepository
    ) {}
 
    public function execute(AssignFormateurDTO $dto): void
    {
        $classroom = $this->classroomRepository->findById($dto->classroom_id);
        
        if ($classroom) {
            // entity update
            $classroom->assignFormateur($dto->formateur_id);

            // model update
            $this->classroomRepository->assignFormateur($dto->classroom_id, $dto->formateur_id);
        }
    }
}
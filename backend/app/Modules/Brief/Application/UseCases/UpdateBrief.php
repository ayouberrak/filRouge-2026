<?php

namespace App\Modules\Brief\Application\UseCases;

use App\Modules\Brief\Application\DTO\BriefDTO;
use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Domain\Entities\BriefEntity;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;
use Exception;

class UpdateBrief
{
    public function __construct(
        private BriefRepositoryInterface $briefRepository
    ) {}

    public function execute(int $id, BriefDTO $dto): BriefEntity
    {
        $brief = $this->briefRepository->findById($id);
        
        if (!$brief) {
            throw new Exception("Brief not found.");
        }

        $entity = new BriefEntity(
            $id,
            $dto->title,
            $dto->image_url,
            $dto->description,
            $dto->context,
            new BriefDatePeriod($dto->date_start, $dto->date_end),
            new BriefModality($dto->modality),
            new BriefStatus($dto->status),
            $dto->tags ,
            $brief->getFormateurId(),
            $brief->hasQuiz() 
        );

        return $this->briefRepository->save($entity);
    }
}

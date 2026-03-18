<?php

namespace App\Modules\Brief\Application\UseCases;

use App\Modules\Brief\Application\DTO\BriefDTO;
use App\Modules\Brief\Domain\Repositories\BriefRepositoryInterface;
use App\Modules\Brief\Domain\Entities\BriefEntity;
use App\Modules\Brief\Domain\ValueObjects\BriefTitle;
use App\Modules\Brief\Domain\ValueObjects\BriefDatePeriod;
use App\Modules\Brief\Domain\ValueObjects\DifficultyLevel;
use App\Modules\Brief\Domain\ValueObjects\BriefModality;
use App\Modules\Brief\Domain\ValueObjects\BriefStatus;

class CreateBrief
{
    public function __construct(
        private BriefRepositoryInterface $briefRepository
    ) {}

    public function execute(BriefDTO $dto): BriefEntity
    {
        $entity = new BriefEntity(
            null, // ID is null on creation
            new BriefTitle($dto->title),
            $dto->description,
            $dto->objectives,
            new BriefDatePeriod($dto->date_start, $dto->date_end),
            new DifficultyLevel($dto->difficulty),
            new BriefModality($dto->modality),
            new BriefStatus($dto->status),
            $dto->tags ?? [],
            $dto->resources ?? [],
            $dto->formateur_id ?? auth()->id()
        );

        return $this->briefRepository->save($entity);
    }
}

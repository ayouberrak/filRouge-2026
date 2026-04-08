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
            $dto->image_url,
            $dto->description,
            $dto->context,
            $dto->objectives,
            new BriefDatePeriod($dto->date_start, $dto->date_end),
            new DifficultyLevel($dto->difficulty),
            new BriefModality($dto->modality),
            $dto->pedagogical_modalities,
            $dto->evaluation_modalities,
            new BriefStatus($dto->status),
            $dto->points ?? 0,
            $dto->tags ?? [],
            $dto->resources ?? [],
            $dto->deliverables ?? [],
            $dto->performance_criteria ?? [],
            $dto->target_competencies ?? [],
            $dto->formateur_id ?? auth()->id(),
            false // hasQuiz handled separately or updated later
        );

        return $this->briefRepository->save($entity);
    }
}

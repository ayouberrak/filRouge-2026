<?php

namespace App\Modules\Livrable\Application\UseCases;

use App\Modules\Livrable\Domain\Repositories\LivrableRepositoryInterface;
use App\Modules\Livrable\Infrastructure\Models\LivrableModel;

class ListStudentLivrablesUseCase
{
    public function __construct(
        private LivrableRepositoryInterface $repository
    ) {}

    public function execute(int $studentId): array
    {
        // On récupère via Eloquent pour simplifier et retourner les données formatées
        $livrables = LivrableModel::where('student_id', $studentId)
            ->with(['brief', 'responses'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $livrables->toArray();
    }
}

<?php

namespace App\Modules\Report\Http\Controllers;

use App\Modules\Report\Application\UseCases\CreateDailyReportUseCase;
use App\Modules\Report\Domain\Repositories\DailyReportRepositoryInterface;
use App\Modules\Report\Http\Requests\CreateDailyReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyReportController
{
    public function __construct(
        private CreateDailyReportUseCase $createDailyReportUseCase,
        private DailyReportRepositoryInterface $repository
    ) {}

    public function store(CreateDailyReportRequest $request): JsonResponse
    {
        $report = $this->createDailyReportUseCase->execute($request->toDTO());
        return response()->json($report->toArray(), 201);
    }

    public function index(): JsonResponse
    {
        $reports = $this->repository->findAll();
        return response()->json($reports);
    }

    public function getByClassroom(int $classroomId): JsonResponse
    {
        $reports = $this->repository->getByClassroom($classroomId);
        return response()->json(['data' => $reports]);
    }

    public function getStats(int $classroomId): JsonResponse
    {
        $stats = $this->repository->getStats($classroomId);
        return response()->json(['data' => $stats]);
    }
}

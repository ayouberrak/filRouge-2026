<?php

namespace App\Modules\Livrable\Http\Controllers;

use App\Modules\Livrable\Application\UseCases\SubmitLivrableUseCase;
use App\Modules\Livrable\Application\UseCases\ViewLivrableUseCase;
use App\Modules\Livrable\Application\UseCases\AddLivrableReponseUseCase;
use App\Modules\Livrable\Application\UseCases\ListStudentLivrablesUseCase;
use App\Modules\Livrable\Application\UseCases\ListBriefSubmissions;
use App\Modules\Livrable\Http\Requests\SubmitLivrableRequest;
use App\Modules\Livrable\Http\Requests\AddLivrableReponseRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivrableController
{
    public function __construct(
        private SubmitLivrableUseCase $submitLivrableUseCase,
        private ViewLivrableUseCase $viewLivrableUseCase,
        private AddLivrableReponseUseCase $addLivrableReponseUseCase,
        private ListStudentLivrablesUseCase $listStudentLivrablesUseCase,
        private ListBriefSubmissions $listBriefSubmissions
    ) {}

    public function listByBrief(int $briefId): JsonResponse
    {
        try {
            $data = $this->listBriefSubmissions->execute($briefId);
            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $studentId = Auth::id();
            $livrables = $this->listStudentLivrablesUseCase->execute($studentId);
            return response()->json(['data' => $livrables]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(SubmitLivrableRequest $request): JsonResponse
    {
        try {
            $livrable = $this->submitLivrableUseCase->execute($request->toDTO());

            return response()->json($livrable->toArray(), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $livrable = $this->viewLivrableUseCase->execute($id);

        if (!$livrable) {
            return response()->json(['error' => 'Livrable non trouvé'], 404);
        }

        return response()->json($livrable->toArray());
    }

    public function addReponse(AddLivrableReponseRequest $request, int $id): JsonResponse
    {
        try {
            $reponse = $this->addLivrableReponseUseCase->execute($request->toDTO($id));

            return response()->json($reponse->toArray(), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

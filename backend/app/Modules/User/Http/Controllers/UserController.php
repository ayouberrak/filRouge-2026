<?php

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Application\UseCases\CreateUser;
use App\Modules\User\Application\UseCases\UpdateUser;
use App\Modules\User\Application\UseCases\BanUser;
use App\Modules\User\Application\UseCases\GetAllUsers;
use App\Modules\User\Application\UseCases\GetUser;
use App\Modules\User\Http\Requests\CreateUserRequest;
use App\Modules\User\Http\Requests\UpdateUserRequest;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController 
{
    public function __construct(
        private CreateUser $createUserUseCase,
        private UpdateUser $updateUserUseCase,
        private BanUser $banUserUseCase,
        private GetAllUsers $getAllUsersUseCase,
        private GetUser $getUserUseCase
    ) {}

    public function index()
    {
        $users = $this->getAllUsersUseCase->execute();
        return response()->json([
            'users' => UserResource::collection($users)
        ]);
    }

    public function show(int $id)
    {
        $user = $this->getUserUseCase->execute($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function create(CreateUserRequest $request)
    {
        $user = $this->createUserUseCase->execute($request->toDTO());
        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user)
        ], 201);
    }

    public function update(int $id, UpdateUserRequest $request)
    {
        $user = $this->updateUserUseCase->execute($id, $request->toDTO());
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user)
        ]);
    }

    public function ban(int $id)
    {
        $user = $this->banUserUseCase->execute($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'message' => 'User banned successfully',
            'user' => new UserResource($user)
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'phone'        => 'nullable|string|max:20',
            'bio'          => 'nullable|string|max:1000',
            'skills'       => 'nullable|array',
            'github_url'   => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
        ]);

        /** @var \App\Modules\User\Infrastructure\Models\UserModel $user */
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data'    => $user
        ]);
    }
}

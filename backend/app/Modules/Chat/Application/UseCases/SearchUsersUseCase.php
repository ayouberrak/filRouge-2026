<?php

namespace App\Modules\Chat\Application\UseCases;

use App\Modules\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Collection;

class SearchUsersUseCase
{
    /**
     * Search for users by name or email, excluding the current user.
     */
    public function execute(string $query, int $excludeUserId): Collection
    {
        return UserModel::where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->where('id', '!=', $excludeUserId)
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'email', 'role']);
    }
}

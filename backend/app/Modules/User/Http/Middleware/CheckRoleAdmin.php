<?php

namespace App\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = $user ? strtolower($user->role) : null;

        if (!$user || $role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
}

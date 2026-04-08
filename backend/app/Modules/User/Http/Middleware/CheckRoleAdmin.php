<?php

namespace App\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $role = $user ? strtolower($user->role) : null;

        if (!$user || $role !== 'admin') {
            \Illuminate\Support\Facades\Log::warning("Unauthorized admin access attempt", [
                'user_id' => $user?->id,
                'role' => $user?->role
            ]);
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        return $next($request);
    }
}

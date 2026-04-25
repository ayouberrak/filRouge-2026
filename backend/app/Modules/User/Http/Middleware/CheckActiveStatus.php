<?php

namespace App\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $status = $user ? strtolower($user->status) : null;

        $allowedStatuses = ['active', 'activer'];

        if (!$user || !in_array($status, $allowedStatuses)) {
            return response()->json(['message' => 'Account is inactive or banned.'], 403);
        }

        return $next($request);
    }
}

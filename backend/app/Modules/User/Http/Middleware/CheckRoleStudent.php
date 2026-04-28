<?php

namespace App\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'student') {
            return response()->json(['message' => 'Unauthorized. Student access required.'], 403);
        }

        return $next($request);
    }
}

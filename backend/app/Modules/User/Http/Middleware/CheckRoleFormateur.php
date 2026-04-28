<?php

namespace App\Modules\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleFormateur
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'formateur') {
            return response()->json(['message' => 'Unauthorized. Formateur access required.'], 403);
        }

        return $next($request);
    }
}

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role.admin' => \App\Modules\User\Http\Middleware\CheckRoleAdmin::class,
            'role.formateur' => \App\Modules\User\Http\Middleware\CheckRoleFormateur::class,
            'role.student' => \App\Modules\User\Http\Middleware\CheckRoleStudent::class,
            'status.active' => \App\Modules\User\Http\Middleware\CheckActiveStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

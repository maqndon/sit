<?php

use App\Http\Middleware\EnsureUserOwnsProject;
use App\Http\Middleware\EnsureUserOwnsTask;
use App\Http\Middleware\ForceJsonResponse;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            ForceJsonResponse::class,
        ]);
        $middleware->alias([
            'task_owner' => EnsureUserOwnsTask::class,
            'project_owner' => EnsureUserOwnsProject::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Throwable $e, $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->validator->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Handle HttpException for unauthorized access
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return response()->json([
                    'message' => 'Unauthorized action.',
                ], $e->getStatusCode() ?: 403);  // Return the status code from the exception or 403
            }

            // Handle other exceptions
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], $e->getCode() ?: 500);  // Use 500 for general server errors
        });
    })
    ->create();

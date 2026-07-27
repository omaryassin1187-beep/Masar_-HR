<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )

    ->withProviders([
        App\Providers\EventServiceProvider::class,
    ])

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role'                    => RoleMiddleware::class,
            'permission'              => PermissionMiddleware::class,
            'role_or_permission'      => RoleOrPermissionMiddleware::class,
            'require.password.change' => \App\Http\Middleware\RequirePasswordChange::class,
            'require.onboarding'      => \App\Http\Middleware\RequireOnboardingCompletion::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // 1️⃣ معالجة خطأ 404
        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Model Not Found',
                    ], 404);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Route Not Found',
                ], 404);
            }
        });

        // 2️⃣ معالجة أخطاء الصلاحيات 403
        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }
        });

        // 3️⃣ معالجة أخطاء HTTP العامة
        $exceptions->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                $statusCode = $e->getStatusCode();
                
                if ($statusCode === 403) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage() ?: 'This action is unauthorized.',
                    ], 403);
                }

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'HTTP Error Occurred',
                ], $statusCode);
            }
        });

        // ✅ 4️⃣ معالجة أخطاء المصادقة (Authentication) - توكن غير صحيح
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401); // ✅ 401 Unauthorized
            }
        });

        // 5️⃣ شبكة الأمان الخلفية (Fallback) لجميع الأخطاء الأخرى
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                $statusCode = 500;

                if (method_exists($e, 'getStatusCode')) {
                    $statusCode = $e->getStatusCode();
                } elseif (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) {
                    $statusCode = (int) $e->getCode();
                }

                $response = [
                    'success' => false,
                    'message' => config('app.debug') ? $e->getMessage() : 'Something Went Wrong',
                ];

                

                return response()->json($response, $statusCode);
            }
        });
    })
    ->create();
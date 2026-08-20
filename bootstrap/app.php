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
use Illuminate\Validation\ValidationException;
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

    // 1️⃣ معالجة أخطاء التحقق من المدخلات (Validation Errors -> 422) لكل النظام
    $exceptions->renderable(function (ValidationException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        }
    });

    // 2️⃣ معالجة أخطاء عدم العثور على المسار أو الموديل (404)
    $exceptions->renderable(function (NotFoundHttpException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            $isModel = $e->getPrevious() instanceof ModelNotFoundException;
            return response()->json([
                'success' => false,
                'message' => $isModel ? 'Resource Not Found' : 'Route Not Found',
            ], 404);
        }
    });

    // 3️⃣ معالجة أخطاء الصلاحيات (403)
    $exceptions->renderable(function (AuthorizationException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        }
    });

    // 4️⃣ معالجة أخطاء تسجيل الدخول / التوكن (401)
    $exceptions->renderable(function (AuthenticationException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }
    });

    $exceptions->render(function (Throwable $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {

            $statusCode = 500;

            if ($e instanceof ValidationException) {
                $statusCode = 422;
            } elseif (method_exists($e, 'getStatusCode')) {
                $statusCode = $e->getStatusCode();
            } elseif (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) {
                $statusCode = (int) $e->getCode();
            }

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Something Went Wrong',
            ], $statusCode);
        }
    });

    $exceptions->renderable(function (\App\Exceptions\ResignationException $e, $request) {
        if ($request->expectsJson() || $request->is('api/*')) {
            return $e->render($request);
        }
    });

})
    ->create();


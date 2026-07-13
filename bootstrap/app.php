<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )

    ->withProviders([
        App\Providers\EventServiceProvider::class,
    ])

    ->withMiddleware(function (Middleware $middleware) {
        // ✅ دمج جميع الـ الـ aliases في مكان واحد لمنع التكرار وتشتت الذاكرة
        $middleware->alias([
            'role'                    => RoleMiddleware::class,
            'permission'              => PermissionMiddleware::class,
            'role_or_permission'      => RoleOrPermissionMiddleware::class,
            'require.password.change' => \App\Http\Middleware\RequirePasswordChange::class,
            'require.onboarding'      => \App\Http\Middleware\RequireOnboardingCompletion::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // 1️⃣ معالجة خطأ 404 بالتفصيل (من ملف صديقك)
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            // إذا كان سبب الـ 404 هو فشل الـ Route Model Binding
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Model Not Found',
                ], 404);
            }

            // إذا كان الرابط غير موجود أساساً في الـ Routes
            return response()->json([
                'success' => false,
                'message' => 'Route Not Found',
            ], 404);
        });

        // 2️⃣ معالجة أخطاء الصلاحيات والـ 403 (من ملفك)
        $exceptions->render(function (AuthorizationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        });

        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 403) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403);
            }
        });

        // 3️⃣ شبكة الأمان الخلفية (Fallback) لجميع الأخطاء الأخرى غير المتوقعة (مثل 500)
        $exceptions->render(function (Throwable $e, $request) {

            // تحقق ذكي من الـ Status Code؛ إذا لم يكن خطأ HTTP نعتبره 500
            $statusCode = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : (method_exists($e, 'getCode') && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Something Went Wrong',
            ], $statusCode);
        });
    })
    ->create();

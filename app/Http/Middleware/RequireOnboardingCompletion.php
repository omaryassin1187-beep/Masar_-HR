<?php

namespace App\Http\Middleware;

use App\Models\Document;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOnboardingCompletion
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // يُطبَّق على الـ employees فقط
        if (! $user || ! $user->hasRole('employee')) {
            return $next($request);
        }

        // إذا أكمل الـ onboarding مسبقاً — اسمح له
        if ($user->onboarding_completed_at !== null) {
            return $next($request);
        }

        // تحقق من الوثائق الإجبارية
        $uploadedTypes = $user->documents()
            ->whereIn('type', Document::REQUIRED_FOR_ONBOARDING)
            ->pluck('type')
            ->toArray();

        $missingTypes = array_diff(Document::REQUIRED_FOR_ONBOARDING, $uploadedTypes);

        if (! empty($missingTypes)) {
            return response()->json([
                'message'       => 'يجب إكمال الـ onboarding أولاً.',
                'requires_onboarding' => true,
                'missing_documents'   => array_values($missingTypes),
            ], 403);
        }

        // كل الوثائق موجودة — أكمل الـ onboarding تلقائياً
        app(\App\Services\Recruitment\EmployeeOnboardingService::class)
            ->completeOnboarding($user);

        return $next($request);
    }
}

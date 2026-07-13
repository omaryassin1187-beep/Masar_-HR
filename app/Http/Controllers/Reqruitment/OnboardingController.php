<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadDocumentRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use App\Services\EmployeeOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    public function status(): JsonResponse
    {
        $user = auth()->user();

        $uploaded = $user->documents()
            ->whereIn('type', Document::REQUIRED_FOR_ONBOARDING)
            ->pluck('type')
            ->toArray();

        $missing = array_diff(Document::REQUIRED_FOR_ONBOARDING, $uploaded);

        return response()->json([
            'completed'          => $user->onboarding_completed_at !== null,
            'uploaded_documents' => $uploaded,
            'missing_documents'  => array_values($missing),
        ]);
    }

    public function upload(UploadDocumentRequest $request): JsonResponse
    {
        $user = auth()->user();
        $types = ['id_card', 'photo', 'bank_info'];
        $uploaded = [];

        foreach ($types as $type) {
            if ($request->hasFile($type)) {
                $file = $request->file($type);
                $path = $file->store("onboarding/{$user->id}/{$type}", 'private');

                $existing = $user->documents()->where('type', $type)->first();
                if ($existing) {
                    Storage::disk('private')->delete($existing->file_path);
                    $existing->delete();
                }

                $uploaded[] = $user->documents()->create([
                    'type'      => $type,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }
        if ($request->hasFile('professional_files')) {
            foreach ($request->file('professional_files') as $file) {
                $path = $file->store("onboarding/{$user->id}/professional", 'private');
                $uploaded[] = $user->documents()->create([
                    'type'      => 'professional',
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }


        // تحقق من اكتمال الوثائق الإجبارية
        $uploadedTypes = $user->documents()
            ->whereIn('type', Document::REQUIRED_FOR_ONBOARDING)
            ->pluck('type')->toArray();

        $isComplete = empty(array_diff(Document::REQUIRED_FOR_ONBOARDING, $uploadedTypes));

        if ($isComplete && $user->onboarding_completed_at === null) {
            app(EmployeeOnboardingService::class)->completeOnboarding($user);
        }

        return response()->json([
            'message'             => 'Documents uploaded successfully.',
            'documents'           => $uploaded,
            'onboarding_complete' => $isComplete,
        ], 201);
    }
}

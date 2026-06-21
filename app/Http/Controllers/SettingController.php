<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use AuthorizesRequests;

    public function show(): JsonResponse
    {
        $this->authorize('view', Setting::class);

        return response()->json([
            'data' => new SettingResource(Setting::instance()),
        ]);
    }
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $this->authorize('update', Setting::class);

        $setting = Setting::first();
        $setting->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث الإعدادات بنجاح.',
            'data'    => new SettingResource($setting->fresh()),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\profile\StoreProfileRequest;
use App\Http\Requests\profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Models\Profile;
use Exception;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProfileController extends Controller
{

    public function index()
    {
        $profile = Auth::user()->profile;

        // التحقق لضمان عدم حدوث خطأ "property id on null" في الـ Resource إذا كان المستخدم لا يملك بروفايل بعد
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found.'
            ], 404);
        }

        return new ProfileResource($profile);
    }


    public function store(StoreProfileRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        // 1️⃣ التحقق من وجود بروفايل مسبق لهذا المستخدم
        if (Profile::where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a profile. Only one profile per user is allowed.'
            ], 409);
        }

        $validated = $request->validated();

        // 2️⃣ معالجة تاريخ الميلاد بشكل مرن وآمن
        try {
            $birthDate = Carbon::parse($validated['birth_date'])->format('Y-m-d');
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid birth date format.'
            ], 422);
        }

        // 3️⃣ الاستفادة من علاقة العقود لجلب تاريخ التعيين الحقيقي
        // سنبحث عن أول عقد موقع أو العقد الفعال الأحدث للمستخدم
        $latestContract = $user->contracts()
            ->whereNotNull('signed_at') // نضمن أن العقد موقع ومكتمل
            ->latest()                  // جلب العقد الأحدث في حال وجود أكثر من عقد
            ->first();

        // إذا لم يجد عقد موقع، يمكننا وضع تاريخ اليوم كـ Fallback أو إرجاع خطأ حسب منطق النظام لديك
        $hiringDate = $latestContract ? $latestContract->start_date : now()->format('Y-m-d');

        $profileData = [
            'birth_date'   => $birthDate,
            'gender'       => $validated['gender'],
            'phone_number' => $validated['phone_number'],
            'address'      => $validated['address'],
            'user_id'      => $userId,
            'hiring_date'  => $hiringDate // ✅ تم الربط بنجاح مع start_date الخاص بالعقد
        ];

        if ($request->hasFile('picture')) {
            $picturePath = $request->file('picture')->store('profile_pictures', 'public');
            $profileData['picture'] = $picturePath;
        }

        $profile = Profile::create($profileData);

        return response()->json([
            'success' => true,
            'message' => 'Profile created successfully',
            'profile' => new ProfileResource($profile)
        ], 201);
    }


    public function show(string $id)
    {
        $profile = Profile::findOrFail($id);
        return new ProfileResource($profile);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request, string $id)
    {
        $userId = Auth::user()->id;
        $profile = Profile::findOrFail($id);
        if ($profile->user_id != $userId) {
            return response()->json('Unauthenticated.', 403);
        }
        $profile->update($request->validated());
        return new ProfileResource($profile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

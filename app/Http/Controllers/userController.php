<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;

class userController extends Controller
{
public function putUserPassword(Request $request)
{
    $request->validate([
        'email'    => ['required', 'email', 'exists:users,email'],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user->is_first_login === false || $user->password !== null) {
        return response()->json([
            'message' => 'Security alert: Password has already been configured for this account.'
        ], 422);
    }

    $user->update([
        'password'       => Hash::make($request->password),
        'is_first_login' => false,
    ]);

    return response()->json([
        'message' => 'Password set successfully.',
    ], 200);
}
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(
                [
                    'message' => 'Envalid email Or Password. ',
                    'status_code' => 400,
                ],
                400
            );
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
                'status_code' => 404,
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successfully.',
            'data' => [
                'user' => $user,
            ],
            'Token' => $token,
            'status_code' => 200,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Successfuly. ',
            'status_code' => 200,
        ], 200);
    }


    public function getUserById($id)
    {
        $user = User::findOrFail($id);
        return new UserResource($user);
    }


    public function getEmployeesByDepartment($depId)
    {
        $emplyees = User::role('employee')
            ->where('dep_id', $depId)
            ->get();
        return UserResource::collection($emplyees);
    }

    public function getManagerEmployees()
    {
        $manager = auth()->user();

        $employees = User::role('employee')
            ->where('dep_id', $manager->dep_id)
            ->with('profile')

            ->get();

        return UserResource::collection($employees);
    }

    public function getAllEmployees()
    {
        $emplyees = User::role('employee')->get();
        return UserResource::collection($emplyees);
    }


    public function getNotifications(): JsonResponse
    {
        $notifications = Auth::user()->notifications;

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data'    => $notifications,
        ], 200);
    }

    public function markAsRead($id)
    {
        $notification = Auth()->user()->notifications()->findORFail($id);
        $notification->markAsRead();
        return response()->json([
            'message' => 'your notifications ',
            'notification' => $notification
        ], 200);
    }


    public function getUnReadNotification()
    {
        $notification = Auth()->user()->notifications->where('read_at', null);
        if ($notification->isEmpty()) {
            return response()->json([
                'message' => 'no new notification ',
            ], 200);
        }
        return response()->json([
            'message' => 'your notifications ',
            'notification' => $notification
        ], 200);
    }

    public function searchManagerEmployees(Request $request)
    {
        $search = trim($request->search);

        $manager = auth()->user();

        $employees = User::role('employee')
            ->where('dep_id', $manager->dep_id)
            ->where('full_name', 'like', "%{$search}%")
            ->get();

        // إذا لم يجد نتائج مطابقة
        if ($employees->isEmpty()) {

            $employees = User::role('employee')
                ->where('dep_id', $manager->dep_id)
                ->get()
                ->map(function ($employee) use ($search) {

                    similar_text(
                        strtolower($search),
                        strtolower($employee->full_name),
                        $percent
                    );

                    $employee->similarity = $percent;

                    return $employee;
                })
                ->sortByDesc('similarity')
                ->take(5)
                ->values();
        }

        return response()->json($employees);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();
        $user->update([
            'password'       => Hash::make($request->password),
            'is_first_login' => false,
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
    public function searchEmployees(Request $request)
    {
        $search = trim($request->search);


        $employees = User::role('employee')
            ->where('full_name', 'like', "%{$search}%")
            ->get();

        // إذا لم يجد نتائج مطابقة
        if ($employees->isEmpty()) {

            $employees = User::role('employee')
                ->get()
                ->map(function ($employee) use ($search) {

                    similar_text(
                        strtolower($search),
                        strtolower($employee->full_name),
                        $percent
                    );

                    $employee->similarity = $percent;

                    return $employee;
                })
                ->sortByDesc('similarity')
                ->take(5)
                ->values();
        }

        return response()->json($employees);
    }
}

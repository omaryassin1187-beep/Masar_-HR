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

    public function searchEmployees(Request $request)
    {
        $search = trim($request->search);

        $user = auth()->user();

        $query = User::role('employee');

        // المدير يرى موظفي قسمه فقط
        if ($user->hasRole('manager')) {
            $query->where('dep_id', $user->dep_id);
        }

        // Admin و HR يرون جميع الموظفين

        $employees = (clone $query)
            ->where('full_name', 'like', "%{$search}%")
            ->get();

        // إذا لم يجد نتائج مطابقة
        if ($employees->isEmpty()) {

            $employees = $query
                ->get()
                ->map(function ($employee) use ($search) {

                    similar_text(
                        mb_strtolower($search),
                        mb_strtolower($employee->full_name),
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


    //لجلب موظفين + مدير القسم حسب القسم الحالي للمستخدم
    public function getDepartmentUsers(): JsonResponse
    {
        $user = auth()->user();

        $users = User::where('dep_id', $user->dep_id)
            ->where('id', '!=', $user->id)
            ->select('id', 'full_name', 'email', 'dep_id')
            ->with('roles')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name ?? 'employee',
                    'is_manager' => $user->hasRole('manager'),
                ];
            });

        $sorted = $users->sortByDesc('is_manager')->values();

        return response()->json([
            'success' => true,
            'data' => $sorted,
        ]);
    }

    //عدد الموظفين والمدراء في النظام
    public function getUsersCount(): JsonResponse
    {
        $employeesCount = User::role('employee')->count();
        $managersCount = User::role('manager')->count();
        $totalUsers = User::count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'employees_count' => $employeesCount,
                'managers_count' => $managersCount,
                'others_count' => $totalUsers - ($employeesCount + $managersCount),
            ]
        ], 200);
    }

    //لجلب جميع الموظفين (role = employee)
    public function getEmployees(): JsonResponse
    {
        $employees = User::role('employee')->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($employees),
        ], 200);
    }

    //لجلب جميع المدراء (role = manager)
    public function getManagers(): JsonResponse
    {
        $managers = User::role('manager')->get();

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($managers),
        ], 200);
    }

public function getNewHiresThisMonth()
{
    $startOfMonth = now()->startOfMonth();
    $endOfMonth   = now()->endOfMonth();

    $query = User::role('employee')
        ->whereHas('profile', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('hiring_date', [$startOfMonth, $endOfMonth]);
        });

    $employees = $query->with(['department', 'profile'])->get();

    return response()->json([
        'status' => true,
        'count'  => $employees->count(),
        'data'   => UserResource::collection($employees),
    ]);
}
}

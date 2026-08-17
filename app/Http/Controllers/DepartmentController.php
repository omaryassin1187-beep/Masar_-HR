<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * ✅ جلب عدد الأقسام
     */
    public function getDepartmentsCount(): JsonResponse
    {
        $count = Department::count();

        return response()->json([
            'success' => true,
            'data' => [
                'departments_count' => $count,
            ]
        ], 200);
    }

    /**
     * ✅ جلب أسماء جميع الأقسام
     */
    public function getDepartmentNames(): JsonResponse
    {
        $departments = Department::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
        ], 200);
    }

    /**
     * ✅ جلب جميع الأقسام مع تفاصيلها
     */
    public function getAllDepartments(): JsonResponse
    {
        $departments = Department::withCount('users')->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
        ], 200);
    }

    /**
     * ✅ جلب الموظفين حسب القسم مع مديرهم
     */
    public function getDepartmentEmployees($depId): JsonResponse
    {
        $department = Department::with('users')->find($depId);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found.',
            ], 404);
        }

        // ✅ جلب مدير القسم (المستخدم الذي لديه role = manager ونفس dep_id)
        $manager = User::where('dep_id', $depId)
            ->whereHas('roles', fn($q) => $q->where('name', 'manager'))
            ->first();

        // ✅ جلب موظفي القسم (role = employee)
        $employees = User::role('employee')
            ->where('dep_id', $depId)
            ->with('profile')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name,
                ],
                'manager' => $manager ? [
                    'id' => $manager->id,
                    'full_name' => $manager->full_name,
                    'email' => $manager->email,
                ] : null,
                'employees_count' => $employees->count(),
                'employees' => $employees->map(fn($emp) => [
                    'id' => $emp->id,
                    'full_name' => $emp->full_name,
                    'email' => $emp->email,
                    'profile' => $emp->profile,
                ]),
            ]
        ], 200);
    }

    /**
     * ✅ جلب الموظفين حسب القسم (مع إمكانية تحديد القسم)
     */
    public function getEmployeesByDepartment(Request $request): JsonResponse
    {
        $depId = $request->query('dep_id');

        $query = User::role('employee')->with('profile');

        if ($depId) {
            $query->where('dep_id', $depId);
        }

        $employees = $query->get();

        $grouped = $employees->groupBy('dep_id')->map(function ($emps, $depId) {
            $department = Department::find($depId);
            return [
                'department' => $department ? $department->name : 'Unknown',
                'department_id' => $depId,
                'employees' => $emps->map(fn($emp) => [
                    'id' => $emp->id,
                    'full_name' => $emp->full_name,
                    'email' => $emp->email,
                ]),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ], 200);
    }
}

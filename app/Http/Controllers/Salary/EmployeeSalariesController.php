<?php

namespace App\Http\Controllers\Salary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salary\IncreaseHourlyRateRequest;
use App\Http\Resources\Salary\EmployeeSalaryResource;
use App\Models\Salary\Employee_salaries;
use App\Models\Salary\EmployeeSalaries;
use App\Models\User;
use App\Services\SalariesService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;


class EmployeeSalariesController extends Controller
{
    public function __construct(
        protected SalariesService $salaryService,
    ) {}


    public function myBaseSalaries()
    {
        $salaries = auth()->user()
            ->employeeSalaries()
            ->orderByDesc('effective_from')
            ->get();

        return response()->json([
            'success' => true,
            'data' => EmployeeSalaryResource::collection($salaries),
        ]);
    }


    public function employeeBaseSalaries($userId)
    {
        $authUser = auth()->user();

        if ($authUser->hasRole('manager')) {

            // المدير يرى فقط موظفي قسمه
            $employee = User::whereHas('roles', function ($query) {
                $query->where('name', 'employee');
            })
                ->where('dep_id', $authUser->dep_id)
                ->findOrFail($userId);
        } elseif ($authUser->hasRole('HR')) {

            // الـ HR يرى الموظفين والمدراء
            $employee = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['employee', 'manager']);
            })
                ->findOrFail($userId);
        } else {

            // الـ Admin يرى الجميع
            $employee = User::findOrFail($userId);
        }

        $salaries = EmployeeSalaries::where('user_id', $employee->id)
            ->latest('effective_from')
            ->get();

        return response()->json([
            'success' => true,
            'data' => EmployeeSalaryResource::collection($salaries),
        ]);
    }


    public function increaseHourlyRate(IncreaseHourlyRateRequest $request, $userId)
    {
        $data = $request->validated();

        $newSalary = DB::transaction(function () use ($userId, $data) {

            $today = Carbon::today();

            // منع أكثر من زيادة بنفس اليوم
            $salaryUpdatedToday = EmployeeSalaries::where('user_id', $userId)
                ->whereDate('effective_from', $today)
                ->exists();

            if ($salaryUpdatedToday) {
                throw ValidationException::withMessages([
                    'hour_price' => 'Salary has already been updated today.',
                ]);
            }

            $currentSalary = EmployeeSalaries::where('user_id', $userId)
                ->whereNull('effective_to')
                ->lockForUpdate()
                ->firstOrFail();

            // منع تخفيض الراتب أو إبقائه كما هو
            if ($data['hour_price'] <= $currentSalary->hour_price) {
                throw ValidationException::withMessages([
                    'hour_price' => 'The new hourly rate must be greater than the current hourly rate.',
                ]);
            }

            // إغلاق سجل الراتب الحالي
            $currentSalary->update([
                'effective_to' => $today->copy()->subDay(),
            ]);


            return EmployeeSalaries::create([
                'user_id'        => $userId,
                'hour_price'     => $data['hour_price'],
                'currency'       => $currentSalary->currency,
                'effective_from' => $today,
                'effective_to'   => null,
                'reason'         => $data['reason'] ?? null,
            ]);
        });

        $this->salaryService->sendSalaryIncreaseNotifications($newSalary);

        return response()->json([
            'message' => 'Hourly rate increased successfully.',
            'data' => new EmployeeSalaryResource($newSalary),
        ]);
    }
}

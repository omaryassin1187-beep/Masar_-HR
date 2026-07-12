<?php

namespace App\Http\Controllers\Attendance_Leaves;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Attendance_Leaves\Attendance;
use App\Http\Controllers\Controller;
use App\Http\Requests\attendance\CheckinRequest;
use App\Models\Attendance_Leaves\HourlyLeaveEquest;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
    ) {}

    public function checkIn(CheckinRequest $request)
    {

        $data = $request->validated();

        if (! $this->attendanceService->isInsideCompany($data['latitude'], $data['longitude'])) {
            throw ValidationException::withMessages([
                'location' => ['You are outside the company location.'],
            ]);
        }

        $attendance = Attendance::firstOrCreate([
            'user_id' => auth()->id(),
            'date'    => today(),
        ]);

        // لا يسمح بوجود جلسة مفتوحة
        $openSession = $attendance->sessions()
            ->whereNull('check_out')
            ->exists();

        if ($openSession) {
            throw ValidationException::withMessages([
                'check_in' => ['You are already checked in.'],
            ]);
        }

        // عدد الجلسات الحالية
        $sessionCount = $attendance->sessions()->count();

        // عدد الإجازات الساعية المعتمدة
        $hourlyLeavesCount = HourlyLeaveEquest::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->where('status', 'approved')
            ->whereTime('start_time', '<=', now()->format('H:i:s'))
            ->count();

        // الحد الأقصى للجلسات = عدد الإجازات + 1
        if ($sessionCount >= ($hourlyLeavesCount + 1)) {

            throw ValidationException::withMessages([
                'check_in' => [
                    'You are not allowed to check in again without an approved hourly leave.'
                ],
            ]);
        }

        $attendance->sessions()->create([
            'check_in' => now(),
        ]);

        return response()->json([
            'message' => 'Check in completed successfully.',
        ], 200);
    }

    public function checkOut(CheckinRequest $request)
    {
        $data = $request->validated();

        if (! $this->attendanceService->isInsideCompany($data['latitude'], $data['longitude'])) {
            throw ValidationException::withMessages([
                'location' => ['You are outside the company location.'],
            ]);
        }
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->firstOrFail();

        $session = $attendance->sessions()
            ->whereNull('check_out')
            ->latest('check_in')
            ->first();

        if (!$session) {

            throw ValidationException::withMessages([
                'check_out' => [
                    'There is no active check in.'
                ],
            ]);
        }

        $session->update([
            'check_out' => now(),
        ]);

        return response()->json([
            'message' => 'Check out completed successfully.',
        ], 200);
    }

    public function getTodayAttendanceSummary(): array
    {
        $attendances = Attendance::query()
            ->with('user')
            ->whereDate('date', today())
            ->visibleTo(auth()->user())
            ->get();

        return [
            'attendances' => $attendances,
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'leave' => $attendances->where('status', 'leave')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
        ];
    }

    public function getTodayAttendances()
    {
        return Attendance::query()
            ->with('user')
            ->whereDate('date', today())
            ->visibleTo(auth()->user())
            ->orderBy('check_in')
            ->get();
    }

    public function getMyMonthlyAttendances()
    {
        return Attendance::query()
            ->with('user')
            ->where('user_id', auth()->id())
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->orderBy('date')
            ->orderBy('check_in')
            ->get();
    }

    public function getFilteredAttendances(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'nullable|date|after_or_equal:from',
            'status' => 'nullable|in:present,late,leave,absent',
            'dep_id' => 'nullable|exists:departments,id',
        ]);

        $attendances = Attendance::query()
            ->with('user')
            ->visibleTo(auth()->user())
            ->whereDate('date', '>=', $validated['from']);


        if (isset($validated['to'])) {
            $attendances->whereDate('date', '<=', $validated['to']);
        }

        if (isset($validated['status'])) {
            $attendances->where('status', $validated['status']);
        }

        if (isset($validated['dep_id'])) {
            $attendances->whereHas('user', function ($query) use ($validated) {
                $query->where('dep_id', $validated['dep_id']);
            });
        }

        return response()->json([
            'data' => $attendances->get()
        ], 200);
    }
}

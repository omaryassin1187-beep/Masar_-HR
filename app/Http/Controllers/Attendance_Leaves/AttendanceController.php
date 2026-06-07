<?php

namespace App\Http\Controllers\Attendance_Leaves;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Attendance_Leaves\Attendance;
use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{
    

    public function checkIn()
    {
        $attendance = Attendance::where('user_id', Auth()->user()->id)
            ->whereDate('date', today())
            ->firstOrFail();

        if ($attendance->check_in) {
            throw ValidationException::withMessages([
                'check_in' => ['You have already checked in today.'],
            ]);
        }

        $attendance->update([
            'check_in' => now()->format('H:i:s'),
        ]);

        return response()->json([
               'message' => 'Check-in completed successfully.'
            ], 200);
    }

    public function checkOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->firstOrFail();

        if (!$attendance->check_in) {
            throw ValidationException::withMessages([
                'check_out' => ['You must check in before checking out.'],
            ]);
        }

        if ($attendance->check_out) {
            throw ValidationException::withMessages([
                'check_out' => ['You have already checked out today.'],
            ]);
        }

        $attendance->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return response()->json([
            'message' => 'Check-out completed successfully.'
        ], 200);
    }

        public function getTodayAttendanceSummary(): array
    {
        $attendances = Attendance::query()
            ->with('user')
            ->whereDate('date', today())
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
            ->orderBy('check_in')
            ->get();
    }

    public function getTodayDepartmentAttendances()
    {
        $manager = auth()->user();

        return Attendance::query()
            ->with('user')
            ->whereDate('date', today())
            ->whereHas('user', function ($query) use ($manager) {

                $query->where('dep_id', $manager->dep_id)
                    ->role('employee');

            })
            ->orderBy('check_in')
            ->get();
    }
}

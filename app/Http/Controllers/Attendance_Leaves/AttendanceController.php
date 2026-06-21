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

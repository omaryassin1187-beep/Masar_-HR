<?php

namespace App\Http\Controllers\Salary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salary\OverTimeByEmployeeRequest;
use App\Http\Requests\Salary\OverTimeByManagerRequest;
use App\Http\Resources\Salary\OverTimeResource;
use App\Models\Salary\OverTime;
use App\Notifications\Salary\MandatoryOverTimeApprovedForEmployeeNotification;
use App\Notifications\Salary\OverTimeApprovedForManagerNotification;
use App\Notifications\Salary\OverTimeRejectedByHrNotification;
use App\Services\OverTimeService;
use Illuminate\Http\JsonResponse;

class OverTimeController extends Controller
{

    public function __construct(
        protected OverTimeService $overTimeService
    ) {}


    public function rejectByHr(int $id)
    {
        $overtime = OverTime::findOrFail($id);

        if ($overtime->status !== 'pending_hr_approval') {
            return  response()->json(
                [
                    'message' => 'Only overtime requests awaiting HR approval can be rejected.'
                ],
                422
            );
        }

        $overtime->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        // إشعار المدير الذي أنشأ الطلب
        $overtime->requestedBy?->notify(new OverTimeRejectedByHrNotification($overtime));

        return response()->json([
            'message' => 'Overtime request has been rejected successfully.',
            'data'    =>  new OverTimeResource($overtime),
        ], 200);
    }

    public function show(string $id)
    {
        $overTime = OverTime::visibleTo(auth()->user())
            ->findOrFail($id);

        return new OverTimeResource($overTime);
    }

    public function approveByHr(int $id)
    {
        $overtime = OverTime::visibleTo(auth()->user())
            ->findOrFail($id);

        if ($overtime->status !== 'pending_hr_approval') {
            return  response()->json(
                [
                    'message' => 'Only overtime requests awaiting HR approval can be rejected.'
                ],
                422
            );
        }

        $overtime->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // إشعار الموظف
        $overtime->user?->notify(
            new MandatoryOverTimeApprovedForEmployeeNotification($overtime)
        );

        // إشعار المدير الذي أنشأ الطلب
        $overtime->requestedBy?->notify(
            new OverTimeApprovedForManagerNotification($overtime)
        );

        return response()->json([
            'message' => 'Overtime request has been approved successfully.',
            'data'    =>  new OverTimeResource($overtime),
        ], 200);
    }

    public function managerRequests()
    {
        $overTimes = $this->overTimeService->getManagerOverTimeRequests();

        return OverTimeResource::collection($overTimes);
    }

    public function getAllEmployeeOverTimeRequests()
    {
        return OverTime::query()
            ->with([
                'user',
                'requestedBy',
                'approvedBy',
            ])
            ->where('type', 'voluntary')
            ->latest()
            ->get();
    }






    public function storeByManager(OverTimeByManagerRequest $request): JsonResponse
    {

        $overtime = $this->overTimeService
            ->storeByManager($request->validated());

        return response()->json([
            'message' => 'Overtime request has been created successfully.',
            'data'    =>  new OverTimeResource($overtime),
        ], 201);
    }


    public function getMyCreatedOverTimeRequests()
    {
        return OverTime::query()
            ->with([
                'user',
                'requestedBy',
                'approvedBy',
            ])
            ->where('requested_by', auth()->id())
            ->where('type', 'mandatory')
            ->latest()
            ->get();
    }

    public function getMyDepartmentEmployeeOverTimeRequests()
    {
        $manager = auth()->user();

        return OverTime::query()
            ->with([
                'user',
                'requestedBy',
                'approvedBy',
            ])
            ->where('type', 'voluntary')
            ->whereHas('user', function ($query) use ($manager) {
                $query->where('dep_id', $manager->dep_id);
            })
            ->latest()
            ->get();
    }

    public function destroy(int $id)
    {
        $this->overTimeService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Overtime request deleted successfully.',
        ]);
    }

    public function approveByManager(int $id)
    {
        $overTime = $this->overTimeService
            ->approveEmployeeRequest($id);

        return response()->json([
            'message' => 'Overtime request approved successfully.',
            'data' => new OverTimeResource($overTime),
        ]);
    }

    public function rejectByManager(int $id)
    {
        $overTime = $this->overTimeService
            ->rejectEmployeeRequest($id);

        return response()->json([
            'message' => 'Overtime request rejected successfully.',
            'data' => new OverTimeResource($overTime),
        ]);
    }


    public function storeByEmployee(OverTimeByEmployeeRequest $request)
    {
        $overtime = $this->overTimeService->storeByEmployee(
            $request->validated()
        );

        return response()->json([
            'message' => 'Overtime request has been created successfully.',
            'data'    =>  new OverTimeResource($overtime),
        ], 201);
    }

    public function myOverTimes()
    {
        return OverTimeResource::collection(
            OverTime::query()
                ->where('user_id', auth()->id())
                ->with([
                    'user',
                    'requestedBy',
                    'approvedBy',
                ])
                ->latest()
                ->get()
        );
    }
}

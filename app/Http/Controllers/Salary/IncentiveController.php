<?php

namespace App\Http\Controllers\Salary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Salary\StoreIncentiveRequest;
use App\Http\Requests\Salary\UpdateIncentiveRequest;
use App\Http\Resources\Salary\IncentiveResource;
use App\Models\Salary\Incentive;
use App\Notifications\Salary\IncentiveCreatedNotification;
use App\Notifications\Salary\IncentiveDeletedNotification;
use App\Notifications\Salary\IncentiveUpdatedNotification;
use Exception;

class IncentiveController extends Controller
{
    public function index()
    {
        $incentives = Incentive::with('user')
            ->latest('date')
            ->get();

        return IncentiveResource::collection($incentives);
    }

    public function store(StoreIncentiveRequest $request)
    {
        $incentive = Incentive::create($request->validated());
        $incentive->user->notify(new IncentiveCreatedNotification($incentive));
        return response()->json([
            'message' => 'Incentive created successfully.',
            'data' => new IncentiveResource($incentive),
        ], 201);
    }

    public function show(int $id)
    {
        $incentive = Incentive::with('user')->findOrFail($id);

        return response()->json([
            'data' => new IncentiveResource($incentive),
        ]);
    }

    public function update(
        UpdateIncentiveRequest $request,
        int $id
    ) {
        $incentive = Incentive::findOrFail($id);
        if ($incentive->is_paid) {
            throw new Exception('Paid incentives cannot be updated.');
        }

        $incentive->update($request->validated());
        $incentive->user->notify(new IncentiveUpdatedNotification($incentive));

        return response()->json([
            'message' => 'Incentive updated successfully.',
            'data' => new IncentiveResource($incentive),
        ], 200);
    }

    public function destroy(int $id)
    {
        $incentive = Incentive::findOrFail($id);
        if ($incentive->is_paid) {
            throw new Exception('Paid incentives cannot be deleted.');
        }
        $incentive->user->notify(new IncentiveDeletedNotification($incentive));

        $incentive->delete();

        return response()->json([
            'message' => 'Incentive created successfully.',
        ], 200);
    }

    public function myIncentives()
    {
        $incentives = Incentive::query()
            ->where('user_id', auth()->id())
            ->latest('date')
            ->get();

        return response()->json([
            'data' => IncentiveResource::collection($incentives),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Salary;

use App\Http\Requests\Salary\StoreDeductionRequest;
use App\Models\Salary\Deduction;
use App\Services\SalariesService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Salary\DeductionResource;
use Illuminate\Http\JsonResponse;

class DeductionController extends Controller
{

    public function __construct(
        protected SalariesService $salaryService,
    ) {}


    public function store(StoreDeductionRequest $request): JsonResponse
    {
        $deduction = Deduction::create($request->validated());

        $this->salaryService->notifyEmployeeAboutDeduction($deduction);

        return response()->json([
            'message' => 'Deduction created successfully.',
            'data'    =>  new DeductionResource($deduction),
        ], 201);
    }

    public function myDeduction()
    {
        $deductions = Deduction::where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => DeductionResource::collection($deductions),
        ]);
    }

    public function employeeDeductions($userId)
    {
        $deductions = Deduction::visibleTo(auth()->user())
            ->where('user_id', $userId)
            ->latest('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => DeductionResource::collection($deductions),
        ]);
    }
}

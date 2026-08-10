<?php

namespace App\Http\Controllers\Salary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Salary\PayrollCurrentResource;
use App\Http\Resources\Salary\PayrollResource;
use App\Models\Salary\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;

class PayrollController extends Controller
{


    // public function validate(Payroll $payroll): JsonResponse
    // {
    //     $validation = $this->validationService->validate($payroll);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $validation,
    //     ]);
    // }

    public function __construct(
        protected PayrollService $payrollService,
    ) {}

    /**
     * Current payroll cycle.
     */
    public function current(): PayrollCurrentResource
    {
        $payroll = $this->payrollService->current();

        $validation = $this->payrollService->validate($payroll);

        return new PayrollCurrentResource([
            'payroll' => $payroll,
            ...$validation,
        ]);
    }

    /**
     * Payroll history.
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return PayrollResource::collection(
            Payroll::query()
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->get()
        );
    }

    /**
     * Generate current payroll.
     */
    public function generate(): JsonResponse
    {
       $payroll= $this->payrollService->generate();

        return response()->json([
            'message' => 'Payroll generated successfully.',
            'data' => new PayrollResource($payroll)
        ]);
    }
}

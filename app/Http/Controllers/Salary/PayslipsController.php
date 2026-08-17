<?php

namespace App\Http\Controllers\Salary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Salary\PayslipResource;
use App\Models\Salary\Payslip;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;

class PayslipsController extends Controller
{

    public function __construct(
        protected PayrollService $payrollService
    ) {}

    public function myPayslips()
    {
        $payslips = Payslip::query()
            ->with([
                'user.department',
                'payroll'
            ])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();


        if ($payslips->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payslips found.'
            ], 404);
        }


        return response()->json([
            'success' => true,
            'data' => PayslipResource::collection($payslips)
        ]);
    }


    public function show(int $id)
    {
        $payslip = Payslip::query()
            ->with([
                'user.department',
                'payroll',
            ])
            ->accessibleBy(auth()->user())
            ->find($id);

        if (! $payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new PayslipResource($payslip),
        ]);
    }

    public function current()
    {
        $payroll = $this->payrollService->current();

        $payslips = Payslip::query()
            ->with([
                'user.department',
                'payroll',
            ])
            ->where('payroll_id', $payroll->id)
            //->accessibleBy(auth()->user())
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => PayslipResource::collection($payslips),
        ]);
    }

    public function summary()
    {
        $payroll = $this->payrollService->current();

        $summary = Payslip::query()
            ->where('payroll_id', $payroll->id)
            ->accessibleBy(auth()->user())
            ->selectRaw('
            COALESCE(SUM(base_salary), 0) as total_base_salary,
            COALESCE(SUM(deductions_amount), 0) as total_deductions,
            COALESCE(SUM(incentive_amount), 0) as total_incentives,
            COALESCE(SUM(net_salary), 0) as total_net_salary
        ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    public function download(int $id)
    {
        $payslip = Payslip::query()
            ->with([
                'user.department',
                'payroll',
            ])
            ->accessibleBy(auth()->user())
            ->find($id);

        if (! $payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found.',
            ], 404);
        }

        $pdf = Pdf::loadView('payslips.pdf', [
            'payslip'    => $payslip,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(
            "Payslip_{$payslip->payroll->month}_{$payslip->payroll->year}.pdf"
        );
    }

    public function preview(int $id)
    {
        $payslip = Payslip::query()
            ->with([
                'user.department',
                'payroll',
            ])
            ->accessibleBy(auth()->user())
            ->find($id);

        if (! $payslip) {
            return response()->json([
                'success' => false,
                'message' => 'Payslip not found.',
            ], 404);
        }

        $pdf = Pdf::loadView('payslips.pdf', [
            'payslip'    => $payslip,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream(
            "Payslip_{$payslip->payroll->month}_{$payslip->payroll->year}.pdf"
        );
    }
}

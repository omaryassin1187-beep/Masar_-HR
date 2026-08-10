<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,


            'employee' => [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'department' => $this->user->department?->name,
            ],


            'payroll' => [
                'id' => $this->payroll->id,
                'month' => $this->payroll->month,
                'year' => $this->payroll->year,
                'status' => $this->payroll->status,
            ],


            'salary_details' => [

                'hourly_rate' => $this->hourly_rate,

                'working_hours_per_day' =>
                $this->working_hours_per_day,

                'working_days' =>
                $this->working_days,


                'base_salary' =>
                $this->base_salary,


                'overtime_amount' =>
                $this->overtime_amount,


                'incentive_amount' =>
                $this->incentive_amount,


                'deductions_amount' =>
                $this->deductions_amount,


                'unpaid_leave' => [
                    'days' =>
                    $this->unpaid_leave_days,

                    'amount' =>
                    $this->unpaid_leave_amount,
                ],


                'gross_salary' =>
                $this->gross_salary,


                'net_salary' =>
                $this->net_salary,
            ],


            'notes' => $this->notes,


            
        ];
    }
}

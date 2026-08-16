<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollCurrentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payroll' => [
                'id' => $this['payroll']->id,
                'month' => $this['payroll']->month,
                'year' => $this['payroll']->year,
                'status' => $this['payroll']->status,
            ],

            'ready' => $this['ready'],

            'summary' => $this['summary'],

            'errors' => $this['errors'],

            'total salaries' => $this['total salaries'],
        ];
    }
}
<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollCurrentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            
            'payroll' => new PayrollResource($this['payroll']),

            'ready' => $this['ready'],

            'summary' => $this['summary'],

            'errors' => $this['errors'],
       
        ];
    }
}

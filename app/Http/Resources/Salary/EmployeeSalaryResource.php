<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'             => $this->id,
            'name'           => $this->user?->full_name,
            'hour_price'     => $this->hour_price,
            'currency'       => $this->currency,
            'effective_from' => $this->effective_from,
            'effective_to'   => $this->effective_to,
            'reason'         => $this->reason,

        ];
    }
}

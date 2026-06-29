<?php

namespace App\Http\Resources\contract;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                      => $this->id,
            'user' => [
                'id'        => $this->user->id,
                'full_name' => $this->user->full_name,
                'email'     => $this->user->email,
            ],
            'department' => $this->user->department->name ?? 'N/A',

            'status'                  => $this->status,
            'start_date'              => $this->start_date->format('Y-m-d'),
            'end_date'                => $this->end_date->format('Y-m-d'),
            'probation_ends_at'       => $this->probationEndsAt()->format('Y-m-d'),
            'is_in_probation'         => $this->isInProbation(),
            'hour_price'              => $this->hour_price,
            'working_hours_per_day'   => $this->working_hours_per_day,
            'weekend_days'            => $this->weekend_days,
            'estimated_monthly_salary' => $this->estimatedMonthlySalary(),
            'termination_notice_days' => $this->termination_notice_days,
            'jurisdiction'            => $this->jurisdiction,
            'signed_at'               => $this->signed_at?->format('Y-m-d'),
        ];
    }
}

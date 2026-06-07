<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'status'                  => $this->status,
            'hour_price'              => $this->hour_price,
            'start_date'              => $this->start_date->format('Y-m-d'),
            'weekend_days'            => $this->weekend_days,
            'working_hour_per_day'    => $this->working_hour_per_day,
            'estimated_monthly_salary'=> $this->estimatedMonthlySalary(),
            'candidate' => [
                'id'        => $this->candidate->id,
                'full_name' => $this->candidate->full_name,
                'email'     => $this->candidate->email,
            ],
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

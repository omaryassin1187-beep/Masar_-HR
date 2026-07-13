<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractRenewalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'contract_id'     => $this->contract_id,
            'user'            => $this->when($this->relationLoaded('user'), [
                'id'        => $this->user->id ?? null,
                'full_name' => $this->user->full_name ?? 'N/A',
                'email'     => $this->user->email ?? 'N/A',
            ]),
            'created_by'      => $this->when($this->relationLoaded('createdBy'), [
                'id'        => $this->createdBy->id ?? null,
                'full_name' => $this->createdBy->full_name ?? 'N/A',
            ]),
            'new_start_date'  => $this->new_start_date?->format('Y-m-d'),
            'new_end_date'    => $this->new_end_date?->format('Y-m-d'),
            'new_hour_price'  => $this->new_hour_price,
            'new_weekend_days' => $this->new_weekend_days,
            'new_working_hours' => $this->new_working_hours_per_day,
            'status'          => $this->status,
            'employee_response_at' => $this->employee_response_at?->toDateTimeString(),
            'expires_at'      => $this->expires_at?->toDateTimeString(),
            'created_at'      => $this->created_at?->toDateTimeString(),
        ];
    }
}

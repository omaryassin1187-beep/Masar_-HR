<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OverTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->user?->full_name,
            'date'                  => $this->date,
            'start_time'            => $this->start_time,
            'end_time'              => $this->end_time,
            'type'                  => $this->type,
            'hour_price'            => $this->hour_price,
            'status'                => $this->status,
            'amount'                => $this->amount,
            'requested_by'          => $this->requestedBy?->full_name,
            'notes'                 => $this->notes,
            'approved_by'           => $this->approvedBy?->full_name,
        ];
    }
}

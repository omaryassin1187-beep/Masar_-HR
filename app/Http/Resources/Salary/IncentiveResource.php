<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncentiveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->user?->full_name,
            'date'    => $this->date,
            'amount'  => $this->amount,
            'reason'  => $this->reason,
            'is_paid' => $this->is_paid,
        ];
    }
}

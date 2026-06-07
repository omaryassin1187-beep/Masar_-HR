<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HourlyLeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
             'id'            =>$this->id,
             'name'          =>$this->user?->full_name,
             'date'          =>$this->date,
             'start_time'    =>$this->start_time,
             'end_time'      =>$this->end_time,
             'reason'        =>$this->reason,
             'status'        =>$this->status,
             
        ];
    }
}

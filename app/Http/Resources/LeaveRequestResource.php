<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\AttendanceService;
use App\Services\LeaveRequestService;

class LeaveRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        $dates = app(LeaveRequestService::class)
            ->calculateLeaveDates(
                $this->start_date,
                $this->days_count
            );


        return [
             'id'            =>$this->id,
             'name'          =>$this->user?->full_name,
             'type'          =>$this->type,
             'start_date'    =>$this->start_date,
             'days_count'    =>$this->days_count,
             'reason'        =>$this->reason,
             'status'        =>$this->status,
             'end_date'      => $dates['end_date'],
            'return_date'    => $dates['return_date'],
        ];
    }
}

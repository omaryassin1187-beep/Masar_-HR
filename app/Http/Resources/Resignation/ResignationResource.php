<?php

namespace App\Http\Resources\Resignation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResignationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'type' => $this->type,

            'reason'           => $this->reason,
            'last_working_day' => $this->last_working_day?->toDateString(),
            'status'           => $this->status,

            'employee' => $this->whenLoaded('employee') && $this->employee
                ? [
                    'id'        => $this->employee->id,
                    'full_name' => $this->employee->full_name,
                    'job_title' => $this->employee->job_title,
                ]
                : null,

            'hr_classification'       => $this->when(
                $this->isImmediate(),
                $this->hr_classification
            ),
            'hr_classification_notes' => $this->when(
                $this->isImmediate(),
                $this->hr_classification_notes
            ),
            'notice_period_treatment' => $this->when(
                $this->isImmediate(),
                $this->notice_period_treatment
            ),
            'classified_by' => $this->whenLoaded('classifiedBy') && $this->classifiedBy
                ? [
                    'id'        => $this->classifiedBy->id,
                    'full_name' => $this->classifiedBy->full_name,
                ]
                : null,
            'classified_at' => $this->classified_at?->toIso8601String(),

            'manager_notified_at'    => $this->manager_notified_at?->toIso8601String(),
            'contract_terminated_at' => $this->contract_terminated_at?->toIso8601String(),

            'documents' => $this->whenLoaded(
                'documents',
                fn() =>
                $this->documents->map(fn($doc) => [
                    'id'        => $doc->id,
                    'file_name' => $doc->file_name,
                    'file_path' => $doc->file_path,
                ])
            ),

            'settlement' => $this->whenLoaded('settlement') && $this->settlement
                ? [
                    'annual_leave_days'         => $this->settlement->annual_leave_days,
                    'annual_leave_amount'       => $this->settlement->annual_leave_amount,
                    'sick_leave_days'           => $this->settlement->sick_leave_days,
                    'sick_leave_amount'         => $this->settlement->sick_leave_amount,
                    'notice_period_amount'      => $this->settlement->notice_period_amount,
                    'end_of_service_gratuity'   => $this->settlement->end_of_service_gratuity,
                    'total_compensation_amount' => $this->settlement->total_compensation_amount,
                    'emailed_at'                => $this->settlement->emailed_at?->toIso8601String(),
                ]
                : null,

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

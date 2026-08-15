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

            'payroll_settled'          => $this->payroll_settled,
            'final_settlement_amount'  => $this->final_settlement_amount,

            'documents' => $this->whenLoaded('documents', fn () =>
                $this->documents->map(fn ($doc) => [
                    'id'             => $doc->id,
                    'original_name'  => $doc->original_name,
                    'file_path'      => $doc->file_path,
                ])
            ),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

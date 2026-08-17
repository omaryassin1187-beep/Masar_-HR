<?php

namespace App\Http\Resources\Termination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerminationRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
            ],

            'contract_id' => $this->contract_id,

            'created_by' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->full_name,
                'role' => $this->created_by_role,
            ],

            'type' => $this->type,

            'termination_date' => $this->termination_date,
            'last_working_day' => $this->last_working_day,

            'notice_period_days' => $this->notice_period_days,

            'ready_for_admin' => $this->ready_for_admin,
            'status' => $this->status,

            'approvals' => $this->approvals->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'step' => $approval->step,
                    'role' => $approval->role,
                    'status' => $approval->status,
                    'decision_reason' => $approval->decision_reason,
                    'approved_by' => $approval->approved_by,
                    'approved_at' => $approval->approved_at,
                ];
            }),

            'immediate_termination' => $this->when(
                $this->type === 'immediate',
                function () {
                    return [
                        'id' => $this->immediateTerminationDetail->id,
                        'subtype' => $this->immediateTerminationDetail->subtype,
                        'compensation_amount' => $this->immediateTerminationDetail->compensation_amount,
                        'legal_reason' => $this->immediateTerminationDetail->legal_reason,
                        'documents_url' => $this->immediateTerminationDetail->documents_path
                            ? asset('storage/' . $this->immediateTerminationDetail->documents_path)
                            : null,
                        'notes' => $this->immediateTerminationDetail->notes,
                    ];
                }
            ),
        ];
    }
}

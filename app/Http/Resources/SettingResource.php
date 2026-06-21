<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // ─── أوقات الدوام ────────────────────────────────────────
            'expected_check_in'  => $this->expected_check_in,
            'expected_check_out' => $this->expected_check_out,
            'grace_period'       => $this->grace_period, // بالدقائق

            // ─── الإجازات ────────────────────────────────────────────
            'weekend_days'     => $this->weekend_days,
            'sick_leave_days'  => $this->sick_leave_days,
            'annual_leave_days' => $this->annual_leave_days,

            // ─── التوظيف والعقود ─────────────────────────────────────
            'probation_period_days'   => $this->probation_period_days,
            'termination_notice_days' => $this->termination_notice_days,
            'jurisdiction'            => $this->jurisdiction,

            // ─── المالية ─────────────────────────────────────────────
            'currency' => $this->currency,

            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}

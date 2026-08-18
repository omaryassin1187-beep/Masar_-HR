<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // ─── أوقات الدوام ────────────────────────────────────────
            'expected_check_in'  => $this->expected_check_in,
            'expected_check_out' => $this->expected_check_out,
            'grace_period'       => (int) $this->grace_period, // بالدقائق

            // ─── الإجازات ────────────────────────────────────────────
            'weekend_days'      => $this->weekend_days,
            'sick_leave_days'   => (int) $this->sick_leave_days,
            'annual_leave_days' => (int) $this->annual_leave_days,

            // ─── التوظيف والعقود ─────────────────────────────────────
            'probation_period_days'   => (int) $this->probation_period_days,
            'termination_notice_days' => (int) $this->termination_notice_days,
            'jurisdiction'            => $this->jurisdiction,

            // ─── المالية ومكافأة نهاية الخدمة ────────────────────────
            'currency'                        => $this->currency,
            'end_of_service_months_per_year' => (int) $this->end_of_service_months_per_year,

            // ─── الموقع الجغرافي وبصمة التواجد ────────────────────────
            'company_latitude'  => (float) $this->company_latitude,
            'company_longitude' => (float) $this->company_longitude,
            'allowed_radius'    => (int) $this->allowed_radius, // بالمتر

            // ─── إعدادات تقييم الأداء ────────────────────────────────
            'eval_task_quality_weight'       => (float) $this->eval_task_quality_weight,
            'eval_task_ontime_weight'        => (float) $this->eval_task_ontime_weight,
            'eval_attendance_weight'         => (float) $this->eval_attendance_weight,
            'eval_salary_increase_threshold' => (float) $this->eval_salary_increase_threshold,
            'eval_min_tenure_days'           => (int) $this->eval_min_tenure_days,

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

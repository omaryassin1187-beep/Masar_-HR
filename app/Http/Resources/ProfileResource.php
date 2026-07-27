<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User; // 💡 1. تم إضافة الاستيراد هنا لضمان عدم حدوث خطأ

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 💡 2. جلب معرف القسم بشكل آمن وتخزينه في متغير لتجنب التكرار والانهيار
        $departmentId = $this->user?->dep_id;

        // 💡 3. استعلام المدير بشكل آمن تماماً بشرط وجود قسم للمستخدم أولاً
        $managerName = 'Not assigned';
        if ($departmentId) {
            $managerName = User::role('manager')
                ->where('dep_id', $departmentId)
                ->first()?->full_name ?? 'Not assigned';
        }

        return [
            'id' => $this->id,

            'user_id' => $this->user?->id,
            'user_name' => $this->user?->full_name,
            'user_email' => $this->user?->email,

            'birth_date' => $this->birth_date,
            'gender' => $this->gender,
            'phone_number' => $this->phone_number,
            'address' => $this->address,

            'picture' => $this->picture ? asset('storage/' . $this->picture) : null,

            'hiring_date' => $this->hiring_date,

            'department' => $this->user?->department?->name, // آمن تماماً 
            'manager' => $managerName, // آمن ومحمي من الـ Null
        ];
    }
}
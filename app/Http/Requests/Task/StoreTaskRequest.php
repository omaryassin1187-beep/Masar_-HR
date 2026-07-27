<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => [
                'required',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $manager = auth()->user();
                    $employee = User::find($value);

                    if (!$employee) {
                        $fail('The selected employee does not exist.');
                        return;
                    }

                    // ✅ منع إسناد مهمة لموظف خارج قسم المدير
                    if ($manager->hasRole('manager') && $employee->dep_id !== $manager->dep_id) {
                        $fail('You can only assign tasks to employees in your department.');
                    }

                    // ✅ منع إسناد مهمة لمدير آخر أو لـ HR/Admin
                    if ($employee->hasAnyRole(['manager', 'HR', 'admin'])) {
                        $fail('You cannot assign tasks to managers, HR, or admin.');
                    }
                    $exists = Task::where('assigned_to', $value)
                        ->where('title', $this->title)
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if ($exists) {
                        $fail('This task has already been assigned to this employee within the last 24 hours.');
                    }
                },

            ],
            'priority' => ['required', Rule::in([
                Task::PRIORITY_LOW,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_HIGH,
                Task::PRIORITY_URGENT,
            ])],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.exists' => 'The selected employee does not exist.',
            'due_date.after_or_equal' => 'Due date must be today or in the future.',
        ];
    }
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }
}

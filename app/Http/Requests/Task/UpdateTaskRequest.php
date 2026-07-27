<?php

namespace App\Http\Requests\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'assigned_to' => [
                'sometimes',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $manager = auth()->user();
                    $employee = User::find($value);

                    if (!$employee) {
                        $fail('The selected employee does not exist.');
                        return;
                    }

                    // ✅ Prevent assigning task to employee outside manager's department
                    if ($manager->hasRole('manager') && $employee->dep_id !== $manager->dep_id) {
                        $fail('You can only assign tasks to employees in your department.');
                    }

                    // ✅ Prevent assigning task to managers, HR, or admin
                    if ($employee->hasAnyRole(['manager', 'HR', 'admin'])) {
                        $fail('You cannot assign tasks to managers, HR, or admin.');
                    }
                },
            ],
            'priority' => ['sometimes', Rule::in([
                Task::PRIORITY_LOW,
                Task::PRIORITY_MEDIUM,
                Task::PRIORITY_HIGH,
                Task::PRIORITY_URGENT,
            ])],
            'due_date' => ['sometimes', 'date', 'after_or_equal:today'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');

            // ✅ Prevent update after submission
            if ($task && in_array($task->status, [
                Task::STATUS_SUBMITTED,
                Task::STATUS_APPROVED,
                Task::STATUS_REJECTED,
            ])) {
                $validator->errors()->add(
                    'status',
                    'Cannot update a task that has been submitted or reviewed'
                );
            }

            // ✅ Check for duplicate task assignment (optional)
            if ($this->has('assigned_to') && $this->has('title')) {
                $exists = Task::where('assigned_to', $this->assigned_to)
                    ->where('title', $this->title)
                    ->where('id', '!=', $this->route('task')?->id)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'title',
                        'This task has already been assigned to this employee within the last 24 hours.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.string' => 'Task title must be a string',
            'title.max' => 'Task title cannot exceed 255 characters',
            'assigned_to.exists' => 'The selected employee does not exist.',
            'priority.in' => 'Invalid priority value',
            'due_date.date' => 'Invalid due date format',
            'due_date.after_or_equal' => 'Due date must be today or a future date',
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

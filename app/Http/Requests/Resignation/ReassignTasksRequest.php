<?php

namespace App\Http\Requests\Resignation;

use Illuminate\Foundation\Http\FormRequest;

class ReassignTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reassignTasks', $this->route('resignation'));
    }

    public function rules(): array
    {
        return [
            'task_ids'   => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', 'exists:tasks,id'],
        ];
    }
}

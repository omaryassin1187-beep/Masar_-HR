<?php

namespace App\Http\Requests\Complaints;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class MarkUnderReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateStatus', Complaint::class) ?? false;
    }

    public function rules(): array
    {

        return [];
    }
protected function failedAuthorization()
{
    abort(403, 'This action is unauthorized.');
}
}

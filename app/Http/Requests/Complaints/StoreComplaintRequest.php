<?php

namespace App\Http\Requests\Complaints;

use App\Models\Complaint;
use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreComplaintRequest extends FormRequest
{
  public function authorize(): bool
{

    return $this->user()?->can('create', Complaint::class) ?? false;
}

    public function rules(): array
    {

        return [
            'subject_id' => [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $author = $this->user();

                    if ((int) $value === $author->id) {
                        $fail('You cannot file a complaint against yourself.');
                        return;
                    }

                    $subject = User::query()->select(['id', 'dep_id'])->with('roles')->find($value);

                    if (! $subject) {
                        return;
                    }

                    if ($author->hasRole('manager')) {
                        if (! $subject->hasRole('employee')) {
                            $fail('Managers can only file complaints against employees, not other managers.');
                            return;
                        }

                        if ($subject->dep_id !== $author->dep_id) {
                            $fail('Managers can only file complaints against employees in their own department.');
                        }
                    }

                    if ($author->hasRole('employee') && $subject->hasAnyRole(['HR', 'admin'])) {
                        $fail('You cannot file a complaint against HR or Admin through this form.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20',
         function ($attribute, $value, $fail) {
        $exists = Complaint::where('author_id', auth()->id())
            ->where('title', $this->title)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();

        if ($exists) {
            $fail('You have already submitted a similar complaint within the last 24 hours.');
        }
    },
],
        ];
    }
    protected function failedValidation($validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422)
        );
    }

protected function failedAuthorization()
{
    abort(403, 'This action is unauthorized.');
}

    public function messages(): array
    {
        return [
            'description.min' => 'Please provide a more detailed description (at least 20 characters).',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\EmployeeNote;
use App\Models\User;

class EmployeeNoteService
{
    public function create(User $employee, int $authorId, array $data): EmployeeNote
    {
        return EmployeeNote::create([
            'user_id'   => $employee->id,
            'author_id' => $authorId,
            'type'      => $data['type'],
            'content'   => $data['content'],
        ]);
    }
}

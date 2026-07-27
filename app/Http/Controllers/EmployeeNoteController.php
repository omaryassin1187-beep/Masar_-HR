<?php

namespace App\Http\Controllers;

use App\Http\Requests\Evaluation\StoreEmployeeNoteRequest;
use App\Http\Resources\Evaluation\EmployeeNoteResource;
use App\Models\EmployeeNote;
use App\Models\User;
use App\Services\EmployeeNoteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EmployeeNoteController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly EmployeeNoteService $service) {}

    public function index(User $employee)
    {
        $this->authorize('viewAny', [EmployeeNote::class, $employee]);

        $query = $employee->notesReceived()->with('author');

        if (auth()->user()->hasRole('manager')) {
            $query->where('author_id', auth()->id());
        }

        $notes = $query->latest()->get();

        return EmployeeNoteResource::collection($notes);
    }


    public function store(StoreEmployeeNoteRequest $request, User $employee)
    {
        $this->authorize('create', [EmployeeNote::class, $employee]);

        $note = $this->service->create($employee, $request->user()->id, $request->validated());

        return new EmployeeNoteResource($note->load('author'));
    }

}

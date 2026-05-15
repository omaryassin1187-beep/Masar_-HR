<?php

namespace App\Http\Controllers\Reqruitment;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequisitionRequest;
use App\Http\Resources\Recruitment\JobRequisitionResource;
use App\Models\JobRequisition;
use App\Models\User;
use App\Notifications\JobRequisitionSubmittedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class JobRequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobRequisitionRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $requisition = JobRequisition::create([
            'department_id' => $request->user()->dep_id,
            'requested_by'  => $request->user()->id,
            'job_title'     => $request->job_title,
            'description'   => $request->description,
            'experience'    => $request->experience,
        ]);

        $requisition->skills()->attach($request->skills);
    $hrUsers = User::role('hr')->get();
    Notification::send( $hrUsers, new JobRequisitionSubmittedNotification($requisition));


    return response()->json([
            'message' => 'Job requisition submitted successfully',
            'data'    => new JobRequisitionResource($requisition->load('skills', 'requestedBy', 'department')),
        ], 201);
    });

    }


    function getAllRequisitions(): JsonResponse
    {
        $requisitions = JobRequisition::with('skills', 'requestedBy', 'department')->get();
        return response()->json([
            'message' => 'Job requisitions retrieved successfully',
            'data'    => JobRequisitionResource::collection($requisitions),
        ], 200);
    }


    public function getNotifications(): JsonResponse
{
    $notifications = Auth::user()->notifications;

    return response()->json([
        'message' => 'Notifications retrieved successfully',
        'data'    => $notifications,
    ], 200);
}




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

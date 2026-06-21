<?php

namespace App\Http\Controllers\Reqruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfferRequest;
use App\Http\Resources\OfferResource;
use App\Models\JobPosting;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class OfferController extends Controller
{
        use AuthorizesRequests;

    public function __construct(private readonly OfferService $service) {}


    public function store(
        StoreOfferRequest $request,
        JobPosting $jobPosting
    ): JsonResponse {
        $this->authorize('create', [Offer::class, $jobPosting]);

        $offer = $this->service->send($jobPosting, $request->validated());

        return response()->json([
            'message' => 'تم إرسال العرض الوظيفي بنجاح.',
            'data'    => new OfferResource($offer),
        ], 201);
    }

    public function respond(Request $request, Offer $offer): \Illuminate\Http\Response
    {

        if (! $request->hasValidSignature()) {
            abort(403, 'هذا الرابط غير صالح أو انتهت صلاحيته.');
        }

        $action = $request->query('action');

        if (! in_array($action, ['accept', 'reject'])) {
            abort(400, 'إجراء غير صحيح.');
        }

        $message = $this->service->respond($offer, $action);

        return response()->view('emails.response', [
            'message' => $message,
            'action'  => $action,
        ]);
    }


    public function index(JobPosting $jobPosting): JsonResponse
    {
        $this->authorize('viewAny', Offer::class);

        $offers = $jobPosting->offers()
            ->with(['candidate'])
            ->latest()
            ->get();

        return response()->json([
            'data' => OfferResource::collection($offers),
        ]);
    }
}

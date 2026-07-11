<?php

namespace App\Http\Controllers;

use App\Models\{Contract, ContractRenewal};
use App\Services\ContractRenewalService;
use App\Http\Requests\contract_renewal\StoreContractRenewalRequest;
use App\Http\Resources\ContractRenewalResource;
use App\Http\Resources\contract\ContractResource;
use App\Notifications\contracts\ContractNonRenewableNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\{JsonResponse, Request, Response};

class ContractRenewalController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ContractRenewalService $renewalService
    ) {}

    public function store(StoreContractRenewalRequest $request, Contract $contract): JsonResponse
    {
        $this->authorize('create', ContractRenewal::class);

        if ($contract->status === Contract::STATUS_NON_RENEWABLE) {
            return response()->json([
                'message' => 'Cannot send renewal request. This contract is marked as non-renewable.'
            ], 422);
        }

        if ($contract->status === Contract::STATUS_EXPIRED) {
            return response()->json([
                'message' => 'Cannot renew an expired contract.'
            ], 422);
        }

        $pending = $contract->renewals()
            ->where('status', ContractRenewal::STATUS_PENDING)
            ->first();

        if ($pending) {
            return response()->json([
                'message' => 'A pending renewal request already exists for this contract.'
            ], 422);
        }

        $accepted = $contract->renewals()
            ->where('status', ContractRenewal::STATUS_ACCEPTED)
            ->whereDate('new_end_date', '>', $contract->end_date)
            ->exists();

        if ($accepted) {
            return response()->json([
                'message' => 'This contract is already renewed for the upcoming period.'
            ], 422);
        }
        // ContractRenewalController@store

        if ($request->new_start_date <= $contract->end_date) {
            return response()->json([
                'message' => 'New start date must be after current contract end date.'
            ], 422);
        }

        $renewal = $this->renewalService->createRenewal(
            $contract,
            $request->validated()
        );

        return response()->json(
            new ContractRenewalResource($renewal),
            201
        );
    }

    public function respond(Request $request, ContractRenewal $renewal): Response
    {
        $expectedSignature = hash_hmac(
            'sha256',
            $renewal->id . $renewal->user->email,
            config('app.key')
        );

        if (! hash_equals($expectedSignature, (string) $request->query('signature'))) {
            abort(403, 'Invalid link.');
        }

        $action = $request->query('action');
        $this->renewalService->respond($renewal, $action);

        return response()->view('emails.renewal-response', [
            'action'  => $action,
            'renewal' => $renewal,
        ]);
    }

    public function expiringSoon(): JsonResponse
    {
        $this->authorize('viewAny', ContractRenewal::class);

        $contracts = Contract::with(['user', 'user.department'])
            ->where('status', Contract::STATUS_ACTIVE)
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->whereDate('end_date', '>=', now())
            ->whereDoesntHave('renewals', function ($q) {
                $q->where('status', ContractRenewal::STATUS_PENDING);
            })
            ->orderBy('end_date')
            ->get();

        return response()->json(
            ContractResource::collection($contracts)
        );
    }

    public function rejectRenewal(Contract $contract): JsonResponse
    {
        $this->authorize('create', ContractRenewal::class);

        $hasPending = $contract->renewals()
            ->where('status', ContractRenewal::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'message' => 'Cannot mark as non-renewable while a renewal request is pending.'
            ], 422);
        }

        $hasAccepted = $contract->renewals()
            ->where('status', ContractRenewal::STATUS_ACCEPTED)
            ->whereDate('new_end_date', '>', $contract->end_date)
            ->exists();

        if ($hasAccepted) {
            return response()->json([
                'message' => 'Cannot reject renewal. Employee already accepted a renewal for the upcoming period.'
            ], 422);
        }

        $contract->update(['status' => Contract::STATUS_NON_RENEWABLE]);

        $contract->user->notify(new ContractNonRenewableNotification($contract));

        return response()->json(['message' => 'Contract marked as non-renewable.']);
    }
}

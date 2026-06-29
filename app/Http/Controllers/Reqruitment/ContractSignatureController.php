<?php

namespace App\Http\Controllers\Reqruitment;

use App\Events\CandidateSignedContract;
use App\Events\HrRejectedContract;
use App\Events\HrRequestedResignature;
use App\Events\HrSignedContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\HrRejectContractRequest;
use App\Http\Requests\sign_contract\HrSignContractRequest;
use App\Http\Requests\sign_contract\StoreCandidateSignatureRequest;
use App\Models\Contract;
use App\Models\Offer;
use App\Models\Setting;
use App\Models\User;
use App\Services\SignatureStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ContractSignatureController extends Controller
{
    public function __construct(
        private readonly SignatureStorageService $signatures
    ) {}


    public function candidateSign(Request $request, Offer $offer)
    {
        $contract = $offer->contracts()->latest()->first();
        $alreadySigned = $contract && $contract->candidate_signed_at;

        if ($alreadySigned) {
            return view('emails.already_signed', [
                'name' => $offer->candidate->full_name,
            ]);
        }

        if ($alreadySigned) {
            return response()->json([
                'success' => false,
                'message' => 'This link has already been used'
            ], 410);
        }
        if ($request->isMethod('get')) {
            return view('contracts.sign', ['offer' => $offer]);
        }
        if ($alreadySigned) {
            return response()->json([
                'success' => false,
                'message' => 'This link has already been used'
            ], 410);
        }


        $request2 = app(StoreCandidateSignatureRequest::class);
        $validated = $request->validate($request2->rules(), $request2->messages());

        $path = $this->signatures->store($validated['signature'], 'signatures/candidates', "offer_{$offer->id}");

        event(new CandidateSignedContract($offer, $path));

        return response()->json([
            'success' => true,
            'message' => 'Your signature has been received successfully. You will be contacted shortly.'
        ]);
    }

    public function previewOfferContract(Offer $offer)
    {
        $pdf = Pdf::loadView('contracts.preview', [
            'offer'         => $offer,
            'user'          => $offer->candidate,
            'jobTitle'      => $offer->jobPosting->requisition->job_title,
            'endDate'       => $offer->start_date->copy()->addYear(),
            'probationDays' => Setting::instance()->probation_period_days,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("contract_preview_{$offer->id}.pdf");
    }


    public function hrSign(Request $request, Contract $contract, User $hr)
    {
        abort_unless($hr->hasRole('HR'), 403);
        $latestContract = $contract->user->contracts()->latest()->first();

        if ($latestContract && $latestContract->hr_signed_at) {
            return view('emails.already_signed', [
                'name' => $contract->user->full_name,
                'type' => 'hr',
            ]);
        }
        if ($contract->status !== Contract::STATUS_AWAITING_HR_SIGNATURE) {
            return response()->json([
                'success' => false,
                'message' => 'Contract is not awaiting HR signature'
            ], 422);
        }
        if ($request->isMethod('get')) {
            return view('contracts.hr_sign', compact('contract', 'hr'));
        }

        $request2 = app(HrSignContractRequest::class);
        $validated = $request->validate($request2->rules(), $request2->messages());

        $path = $this->signatures->store($validated['signature'], 'signatures/hr', "contract_{$contract->id}");


        event(new HrSignedContract($contract, $path, $hr));

        return response()->json([
            'success' => true,
            'message' => 'Contract signed successfully by HR'
        ]);
    }


    public function hrRequestResign(Request $request, Contract $contract, User $hr)
    {
      if (!$hr->hasRole('HR')) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized. HR role required.'
    ], 403);
}

if ($contract->hr_signed_at) {
    return response()->json([
        'success' => false,
        'message' => 'Contract is fully signed'
    ], 410);
}

if ($contract->status !== Contract::STATUS_AWAITING_HR_SIGNATURE) {
    return response()->json([
        'success' => false,
        'message' => 'Contract is not awaiting HR signature'
    ], 422);
}

        if ($request->isMethod('get')) {
            return view('contracts.hr_resign', compact('contract', 'hr'));
        }


        event(new HrRequestedResignature($contract, $hr));

        return response()->json([
            'success' => true,
            'message' => 'Re-sign request has been sent to the candidate'
        ]);
    }


    public function pendingSignature()
    {
        $contracts = Contract::where('status', Contract::STATUS_AWAITING_HR_SIGNATURE)
            ->whereNotNull('candidate_signed_at')
            ->with(['user', 'offer.jobPosting.requisition'])
            ->latest('candidate_signed_at')
            ->paginate(20);

        return response()->json($contracts);
    }
}

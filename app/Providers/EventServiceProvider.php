<?php

namespace App\Providers;

use App\Events\CandidateSignedContract;
use App\Events\HrRejectedContract;
use App\Events\HrRequestedResignature;
use App\Events\HrSignedContract;
use App\Events\ImmediateResignationSubmitted;
use App\Events\OfferAccepted;
use App\Listeners\CompleteContractAfterHrSignature;
use App\Listeners\CreateOrUpdateContractFromCandidateSignature;
use App\Listeners\NotifyCandidateOfContractRejection;
use App\Listeners\NotifyManagerForTaskReassignment;
use App\Listeners\ResendSignatureRequestToCandidate;
use App\Listeners\SendSignatureRequestToCandidate;
use Illuminate\Support\ServiceProvider;


class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OfferAccepted::class => [
            SendSignatureRequestToCandidate::class,
        ],
        CandidateSignedContract::class => [
            CreateOrUpdateContractFromCandidateSignature::class,
        ],
        HrRequestedResignature::class => [
            ResendSignatureRequestToCandidate::class,
        ],
        HrSignedContract::class => [
            CompleteContractAfterHrSignature::class,
        ],

        ImmediateResignationSubmitted::class => [
            NotifyManagerForTaskReassignment::class,
        ],


    ];
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}

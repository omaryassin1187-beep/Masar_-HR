<?php

namespace App\Providers;

use App\Events\CandidateSignedContract;
use App\Events\HrRejectedContract;
use App\Events\HrRequestedResignature;
use App\Events\HrSignedContract;
use App\Events\OfferAccepted;
use App\Events\TerminationApproved;
use App\Listeners\CompleteContractAfterHrSignature;
use App\Listeners\CreateOrUpdateContractFromCandidateSignature;
use App\Listeners\ExecuteTermination;
use App\Listeners\NotifyCandidateOfContractRejection;
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
        TerminationApproved::class => [
            ExecuteTermination::class,

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

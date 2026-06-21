<?php

namespace App\Listeners;

use App\Events\OfferAccepted;
use App\Services\EmployeeOnboardingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateEmployeeFromCandidate
{
    public function __construct(
        private EmployeeOnboardingService $onboardingService
    ) {}

    public function handle(OfferAccepted $event): void
    {
        $this->onboardingService->createFromOffer($event->offer);
    }
}

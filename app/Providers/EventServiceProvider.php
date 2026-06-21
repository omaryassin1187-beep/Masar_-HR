<?php

namespace App\Providers;

use App\Events\OfferAccepted;
use App\Listeners\CreateEmployeeFromCandidate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
    OfferAccepted::class => [
        CreateEmployeeFromCandidate::class,
    ],
];
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

    }
}

<?php

namespace App\Providers;

use App\Models\JobRequisition;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Policies\InterviewPolicy;
use App\Policies\JobRequisitionPolicy;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Support\Facades\Gate;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         Gate::policy(User::class, LeaveRequestPolicy::class);
         Gate::policy(User::class, InterviewPolicy::class);
         Gate::policy(JobRequisition::class, JobRequisitionPolicy::class);


    }
}

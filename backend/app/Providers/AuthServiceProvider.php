<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Passport::tokensExpireIn(Carbon::now()->addMinutes(10)); // Access Token expiry

        // Passport::refreshTokensExpireIn(Carbon::now()->addDays(7)); // Refresh Token expiry

        Passport::personalAccessTokensExpireIn(Carbon::now()->addMonths(1)); // Personal Access Token expiry

        // ->addMinutes(6)
        // ->addHours(6)
        // ->addDays(6)
        // ->addMonths(6)

    }
}

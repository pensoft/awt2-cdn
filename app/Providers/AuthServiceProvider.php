<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Authentication\UserProvider;
use App\Guards\ApiHeaderGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend('header_provider', function($app, array $config) {
            return app(UserProvider::class);
        });

        Auth::extend('access_header', function ($app, $name, array $config) {
            // automatically build the DI, put it as reference
            $userProvider = app(UserProvider::class);
            $request = app('request');

            return new ApiHeaderGuard($userProvider, $request, $config);
        });
    }
}

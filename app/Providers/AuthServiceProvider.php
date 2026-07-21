<?php

namespace App\Providers;

use App\Auth\NovaSessionGuard;
use App\Auth\NovaUserProvider;
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
         'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        Auth::provider('NovaUserProvider', function ($app, $config) {
            return new NovaUserProvider($app['hash'], $config['model']);
        });

        Auth::extend('jwt', function ($app, $name, array $config) {
            return new NovaSessionGuard(Auth::createUserProvider($config['provider']), $app->request);
        });

        $this->registerPolicies();

        //
    }
}

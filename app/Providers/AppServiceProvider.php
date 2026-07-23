<?php

namespace App\Providers;

use App\Models\Authentication\Account;
use App\Models\Authentication\Authenticatable;
use App\Models\Authentication\Profile;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

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
        Relation::enforceMorphMap([
            'Authenticatable' => Authenticatable::class,
            'Account' => Account::class,
            'Profile' => Profile::class,
        ]);
    }
}

<?php

namespace App\Providers;

use App\DTO\FavoriteGifDTO;
use App\Policies\FavoriteGifPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        FavoriteGifDTO::class => FavoriteGifPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::personalAccessTokensExpireIn(now()->addMinutes(30));
    }
}

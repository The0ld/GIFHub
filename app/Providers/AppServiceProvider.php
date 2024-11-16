<?php

namespace App\Providers;

use App\Clients\GiphyClient;
use App\Contracts\Repositories\{AuthRepositoryInterface, GifRepositoryInterface};
use App\Contracts\Services\{AuthServiceInterface, GifServiceInterface};
use App\Contracts\Clients\GifClientInterface;
use App\Repositories\{AuthRepository, GifRepository};
use App\Services\{AuthService, GifService};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(GifRepositoryInterface::class, GifRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(GifClientInterface::class, GiphyClient::class);
        $this->app->bind(GifServiceInterface::class, GifService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

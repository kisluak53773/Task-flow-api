<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Domain\User\Repository\UserRepositoryInterface;
use Infrastructure\Persistence\Eloquent\EloquentUserRepository;
use Domain\Project\Repository\ProjectRepositoryInterface;
use Infrastructure\Persistence\Eloquent\EloquentProjectRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, EloquentProjectRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

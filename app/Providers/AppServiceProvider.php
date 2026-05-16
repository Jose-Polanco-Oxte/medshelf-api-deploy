<?php

namespace App\Providers;

use App\Core\Shared\Application\EventPublisher;
use App\Core\Shared\Application\TransactionManager;
use App\Providers\Core\LaravelEventPublisher;
use App\Providers\Core\LaravelTransactionManager;
use Illuminate\Support\ServiceProvider;
use URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EventPublisher::class, LaravelEventPublisher::class);
        $this->app->bind(TransactionManager::class, LaravelTransactionManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}

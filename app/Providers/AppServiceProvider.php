<?php

namespace App\Providers;

use App\Core\Shared\Application\EventPublisher;
use App\Core\Shared\Application\TransactionManager;
use App\Providers\Core\LaravelEventPublisher;
use DB;
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
        $this->app->bind(TransactionManager::class, function () {
            return new class implements TransactionManager {
                public function run(callable $callback): mixed
                {
                    return DB::transaction($callback);
                }
            };
        });
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

<?php

namespace App\Providers;

use App\Domains\Models\Share\Clock\Clock;
use App\Domains\Models\Share\Clock\Production\SystemClock;
use App\Infrastructure\Transaction\TransactionExecutorImpl;
use App\UseCase\Share\TransactionExecutor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TransactionExecutor::class, TransactionExecutorImpl::class);
        $this->app->bind(Clock::class, SystemClock::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

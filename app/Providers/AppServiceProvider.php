<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
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
        $this->setTime();

        Queue::looping(function () {
            $this->setTime();
        });
    }

    // Speed time up so for every second, a minute passes
    public function setTime(): void
    {
        if ($running = cache('running')) {
            Carbon::setTestNow(Carbon::createFromTimestamp(time() + (time() - $running) * 60));
        }
    }
}

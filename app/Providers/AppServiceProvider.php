<?php

namespace App\Providers;

use App\Livewire\Synthesizers\MapSynthesizer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use LsvEu\Rivers\Cartography;
use LsvEu\Rivers\Cartography\RiverElement;

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

        RiverElement::macro('convertToCell', function () {
            return (object) (property_exists($this, 'x') ? [
                'id' => $this->id,
                'x' => $this->x,
                'y' => $this->y,
                'type' => match (true) {
                    $this instanceof Cartography\Bridge => 'bridge',
                    $this instanceof Cartography\Launch => 'launch',
                    $this instanceof Cartography\Fork => 'fork',
                    $this instanceof Cartography\Rapid => 'rapid',
                    default => null,
                },
                'ports' => $this instanceof Cartography\Fork ?
                    $this->conditions->map(fn ($condition) => $condition->id)->values() :
                    [],
            ] : ['type' => null]);
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

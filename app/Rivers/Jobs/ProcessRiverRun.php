<?php

namespace App\Rivers\Jobs;

class ProcessRiverRun extends \LsvEu\Rivers\Jobs\ProcessRiverRun
{
    public static function dispatch(...$arguments)
    {
        // Add an artificial delay, so changes are visible
        return parent::dispatch(...$arguments)->delay(90);
    }
}

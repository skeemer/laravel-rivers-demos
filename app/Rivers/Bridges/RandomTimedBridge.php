<?php

namespace App\Rivers\Bridges;

use App\Rivers\Concerns\Positionable;
use App\Rivers\Rafts\UserRaft;
use DateInterval;
use LsvEu\Rivers\Cartography\Bridges\TimedBridge;

class RandomTimedBridge extends TimedBridge
{
    use Positionable;

    public function getDateInterval(?UserRaft $user = null): DateInterval
    {
        $duration = sprintf('PT%dM', rand(5, 30));

        return new DateInterval($duration);
    }
}

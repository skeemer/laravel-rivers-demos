<?php

namespace App\Rivers\Bridges;

use App\Rivers\Rafts\UserRaft;
use DateInterval;
use LsvEu\Rivers\Cartography\Bridge\TimedBridge;

class NameLengthBridge extends TimedBridge
{
    public function getDateInterval(?UserRaft $user = null): DateInterval
    {
        $duration = sprintf('PT%dM', strlen($user->name) - 5);

        return new DateInterval($duration);
    }
}

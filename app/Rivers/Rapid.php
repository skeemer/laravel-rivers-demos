<?php

namespace App\Rivers;

use App\Rivers\Concerns\Positionable;
use LsvEu\Rivers\Cartography\Rapid as BaseRapid;

class Rapid extends BaseRapid
{
    use Positionable;
}

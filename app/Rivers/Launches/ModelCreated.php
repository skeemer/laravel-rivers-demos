<?php

namespace App\Rivers\Launches;

use App\Rivers\Concerns\Positionable;
use LsvEu\Rivers\Cartography\Launches\ModelCreated as BaseLaunch;

class ModelCreated extends BaseLaunch
{
    use Positionable;
}

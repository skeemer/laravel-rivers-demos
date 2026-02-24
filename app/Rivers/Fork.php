<?php

namespace App\Rivers;

use App\Rivers\Concerns\Positionable;
use LsvEu\Rivers\Cartography\Fork as BaseFork;

class Fork extends BaseFork
{
    use Positionable;
}

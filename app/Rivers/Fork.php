<?php

namespace App\Rivers;

use App\Rivers\Concerns\Labelable;
use App\Rivers\Concerns\Positionable;
use LsvEu\Rivers\Cartography\Fork as BaseFork;

class Fork extends BaseFork
{
    use Labelable, Positionable;

    public function getEditorLabel(): string
    {
        return $this->label ?? 'Fork';
    }
}

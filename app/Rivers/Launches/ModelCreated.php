<?php

namespace App\Rivers\Launches;

use App\Rivers\Concerns\Labelable;
use App\Rivers\Concerns\Positionable;
use LsvEu\Rivers\Cartography\Launches\ModelCreated as BaseLaunch;

class ModelCreated extends BaseLaunch
{
    use Labelable, Positionable;

    public function getEditorLabel(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return $this->class ? 'Model created: '.class_basename($this->class) : 'Model created: --';
    }
}

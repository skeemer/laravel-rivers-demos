<?php

namespace App\Rivers\Concerns;

trait Positionable
{
    public int $x = 0;

    public int $y = 0;

    public function hydratePositionable(array $attributes): void
    {
        $this->x = $attributes['x'] ?? $this->x;
        $this->y = $attributes['y'] ?? $this->y;
    }

    public function toArrayPositionable(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }
}

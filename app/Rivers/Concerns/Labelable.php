<?php

namespace App\Rivers\Concerns;

trait Labelable
{
    public ?string $label = null;

    public function hydrateLabelable(array $attributes): void
    {
        $this->label = $attributes['label'] ?? $this->label;
    }

    public function toArrayLabelable(): array
    {
        return [
            'label' => $this->label,
        ];
    }
}

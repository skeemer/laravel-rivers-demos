<?php

namespace App\Rivers\Forks\Conditions;

use App\Rivers\Rafts\UserRaft;
use LsvEu\Rivers\Cartography\Fork\Condition;

class NameSortCondition extends Condition
{
    public ?string $letter;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->letter = $attributes['letter'] ?? '';
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'letter' => $this->letter,
        ];
    }

    public function evaluate(?UserRaft $user = null): bool
    {
        return $user->name < $this->letter;
    }
}

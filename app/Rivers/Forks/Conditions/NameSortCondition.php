<?php

namespace App\Rivers\Forks\Conditions;

use App\Rivers\Enums\NameSortLogic;
use App\Rivers\Rafts\UserRaft;
use LsvEu\Rivers\Cartography\Condition;

class NameSortCondition extends Condition
{
    public ?string $letter;

    public ?NameSortLogic $mode;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->letter = $attributes['letter'] ?? '';
        $this->mode = NameSortLogic::from($attributes['mode'] ?? NameSortLogic::LESS_THAN->value);
    }

    public function toArray(): array
    {
        return parent::toArray() + [
            'letter' => $this->letter,
            'mode' => $this->mode->value,
        ];
    }

    public function evaluate(?UserRaft $user = null): bool
    {
        if (! $user) {
            throw new \Exception('User not found');
        }

        $firstLetter = substr($user->name, 0, 1);

        return match ($this->mode) {
            NameSortLogic::EQUAL => $firstLetter === $this->letter,
            NameSortLogic::GREATER_THAN => $firstLetter > $this->letter,
            NameSortLogic::GREATER_THAN_OR_EQUAL => $firstLetter >= $this->letter,
            NameSortLogic::LESS_THAN => $firstLetter < $this->letter,
            NameSortLogic::LESS_THAN_OR_EQUAL => $firstLetter <= $this->letter,
            NameSortLogic::NOT_EQUAL => $firstLetter !== $this->letter,
            default => false,
        };
    }
}

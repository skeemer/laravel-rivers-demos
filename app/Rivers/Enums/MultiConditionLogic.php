<?php

namespace App\Rivers\Enums;

enum MultiConditionLogic: string
{
    case AND = 'and';
    case OR = 'or';

    public static function isValid(string $value): bool
    {
        return in_array($value, self::cases(), true);
    }

    public static function options(): array
    {
        return [
            self::AND->value => 'All conditions must be true',
            self::OR->value => 'At least one condition must be true',
        ];
    }
}

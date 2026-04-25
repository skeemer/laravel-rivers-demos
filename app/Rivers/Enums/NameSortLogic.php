<?php

namespace App\Rivers\Enums;

enum NameSortLogic: string
{
    case EQUAL = 'eq';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUAL = 'lte';
    case NOT_EQUAL = 'neq';

    public static function options(): array
    {
        return [
            self::LESS_THAN->value => 'Less than',
            self::LESS_THAN_OR_EQUAL->value => 'Less than or equal to',
            self::EQUAL->value => 'Equal to',
            self::GREATER_THAN_OR_EQUAL->value => 'Greater than or equal to',
            self::GREATER_THAN->value => 'Greater than',
            self::NOT_EQUAL->value => 'Not equal to',
        ];
    }
}

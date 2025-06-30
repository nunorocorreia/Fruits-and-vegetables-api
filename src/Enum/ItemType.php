<?php

namespace App\Enum;

use InvalidArgumentException;

enum ItemType: string
{
    case FRUIT = 'fruit';
    case VEGETABLE = 'vegetable';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            'fruit' => self::FRUIT,
            'vegetable' => self::VEGETABLE,
            default => throw new InvalidArgumentException("Invalid item type: {$value}")
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::FRUIT => 'Fruit',
            self::VEGETABLE => 'Vegetable',
        };
    }
}

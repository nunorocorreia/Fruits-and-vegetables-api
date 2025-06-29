<?php

namespace App\Resource;

use App\Enum\ItemType;

class FruitResource extends AbstractResource
{
    protected static function getDefaultType(): ItemType
    {
        return ItemType::FRUIT;
    }
}

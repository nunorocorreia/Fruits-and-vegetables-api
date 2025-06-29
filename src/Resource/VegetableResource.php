<?php

namespace App\Resource;

use App\Entity\Vegetable;
use App\Enum\ItemType;

class VegetableResource extends AbstractResource
{
    protected static function getDefaultType(): ItemType
    {
        return ItemType::VEGETABLE;
    }
} 
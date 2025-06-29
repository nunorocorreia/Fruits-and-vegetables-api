<?php

namespace App\DTO;

use App\Enum\ItemType;
use Symfony\Component\Validator\Constraints as Assert;

class AddFruitDTO extends AddItemDTO
{
    #[Assert\EqualTo(value: ItemType::FRUIT, message: 'Type must be "fruit"')]
    public ?ItemType $type = ItemType::FRUIT;
} 
<?php

namespace App\DTO;

use App\Enum\ItemType;
use Symfony\Component\Validator\Constraints as Assert;

class AddVegetableDTO extends AddItemDTO
{
    #[Assert\EqualTo(value: ItemType::VEGETABLE, message: 'Type must be "vegetable"')]
    public ?ItemType $type = ItemType::VEGETABLE;
} 
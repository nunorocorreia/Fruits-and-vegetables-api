<?php

namespace App\DTO;

use App\Enum\ItemType;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AddItemDTO
{
    #[Assert\NotBlank(message: 'Name is required')]
    public mixed $name = null;

    #[Assert\NotNull(message: 'Type is required')]
    #[Assert\Type(type: ItemType::class, message: 'Type must be a valid item type')]
    public ?ItemType $type = null;

    #[Assert\NotNull(message: 'Quantity is required')]
    #[Assert\Positive(message: 'Quantity must be positive')]
    public mixed $quantity = null;

    #[Assert\Choice(choices: ['g', 'kg'], message: 'Unit must be either "g" or "kg"')]
    public string $unit = 'g';
} 
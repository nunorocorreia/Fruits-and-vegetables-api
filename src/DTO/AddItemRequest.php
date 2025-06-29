<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AddItemRequest
{
    #[Assert\NotBlank(message: 'Name is required')]
    public mixed $name = null;

    #[Assert\NotBlank(message: 'Type is required')]
    #[Assert\Choice(choices: ['fruit', 'vegetable'], message: 'Type must be either "fruit" or "vegetable"')]
    public mixed $type = null;

    #[Assert\NotNull(message: 'Quantity is required')]
    #[Assert\Positive(message: 'Quantity must be positive')]
    public mixed $quantity = null;

    #[Assert\Choice(choices: ['g', 'kg'], message: 'Unit must be either "g" or "kg"')]
    public string $unit = 'g';
} 
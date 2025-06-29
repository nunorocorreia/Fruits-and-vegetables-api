<?php

namespace App\Resource;

use App\Enum\ItemType;

abstract class AbstractResource
{
    public static function toArray(object $entity, string $unit = 'g'): array
    {
        $quantityInGrams = $entity->getQuantity(); // Always stored in grams
        
        // Convert to requested unit for display
        if ($unit === 'kg') {
            $displayQuantity = $quantityInGrams / 1000;
        } else {
            $displayQuantity = $quantityInGrams; // Keep in grams
        }
        
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'type' => $entity->getType(),
            'quantity' => $displayQuantity,
            'unit' => $unit
        ];
    }
    
    public static function collection(array $entities, string $unit = 'g'): array
    {
        return array_map(function (object $entity) use ($unit) {
            return static::toArray($entity, $unit);
        }, $entities);
    }
    
    public static function fromRequest(array $data): array
    {
        // Convert input to grams for storage
        $inputQuantity = $data['quantity'] ?? 0;
        $inputUnit = $data['unit'] ?? 'g';
        
        if ($inputUnit === 'kg') {
            $quantityInGrams = $inputQuantity * 1000;
        } else {
            $quantityInGrams = $inputQuantity; // Already in grams
        }
        
        return [
            'name' => $data['name'] ?? '',
            'type' => $data['type'] ?? static::getDefaultType()->value,
            'quantity' => $quantityInGrams,
            'unit' => 'g' // Always store as grams
        ];
    }

    /**
     * Get the default type for this resource
     */
    abstract protected static function getDefaultType(): ItemType;
} 
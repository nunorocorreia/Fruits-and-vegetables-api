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
            'type' => static::getDefaultType()->value,
            'quantity' => $displayQuantity,
            'unit' => $unit,
            'date_add' => $entity->getDateAdd()?->format('Y-m-d H:i:s'),
            'date_upd' => $entity->getDateUpd()?->format('Y-m-d H:i:s')
        ];
    }

    public static function collection(array $entities, string $unit = 'g'): array
    {
        return array_map(function (object $entity) use ($unit) {
            return static::toArray($entity, $unit);
        }, $entities);
    }

    /**
     * Get the default type for this resource
     */
    abstract protected static function getDefaultType(): ItemType;
}

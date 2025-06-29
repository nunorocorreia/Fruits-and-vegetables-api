<?php

namespace App\Service;

use App\Enum\ItemType;
use App\Service\FruitCollectionManager;
use App\Service\VegetableCollectionManager;

class JsonProcessorService
{
    public function __construct(
        private FruitCollectionManager $fruitCollectionManager,
        private VegetableCollectionManager $vegetableCollectionManager
    ) {
    }

    /**
     * Process the request.json file and return processing results
     */
    public function processFile(string $filePath = 'request.json'): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File {$filePath} not found!");
        }

        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new \RuntimeException('Invalid JSON format: expected an array');
        }

        return $this->processData($data);
    }

    /**
     * Process JSON data array and return processing results
     */
    public function processData(array $data): array
    {
        $fruitsCount = 0;
        $vegetablesCount = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                $result = $this->processItem($item);
                if ($result['type'] === ItemType::FRUIT->value) {
                    $fruitsCount++;
                } elseif ($result['type'] === ItemType::VEGETABLE->value) {
                    $vegetablesCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing '{$item['name']}': " . $e->getMessage();
            }
        }

        return [
            'fruits_count' => $fruitsCount,
            'vegetables_count' => $vegetablesCount,
            'errors' => $errors,
            'success' => empty($errors)
        ];
    }

    /**
     * Process a single item and return the type that was processed
     */
    private function processItem(array $item): array
    {
        if (!isset($item['name'], $item['type'], $item['quantity'], $item['unit'])) {
            throw new \InvalidArgumentException('Missing required fields: name, type, quantity, unit');
        }

        // Validate and convert type using enum
        try {
            $itemType = ItemType::fromString($item['type']);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Invalid type '{$item['type']}' for item '{$item['name']}'");
        }

        $itemData = [
            'name' => $item['name'],
            'type' => $itemType->value,
            'quantity' => $item['quantity'],
            'unit' => $item['unit']
        ];

        if ($itemType === ItemType::FRUIT) {
            $this->fruitCollectionManager->add($itemData);
            return ['type' => ItemType::FRUIT->value];
        } elseif ($itemType === ItemType::VEGETABLE) {
            $this->vegetableCollectionManager->add($itemData);
            return ['type' => ItemType::VEGETABLE->value];
        }

        throw new \InvalidArgumentException("Invalid type '{$item['type']}' for item '{$item['name']}'");
    }
} 
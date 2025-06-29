<?php

namespace App\Tests\App\Service;

use App\Enum\ItemType;
use App\Service\FruitCollectionManager;
use App\Service\JsonProcessorService;
use App\Service\VegetableCollectionManager;
use PHPUnit\Framework\TestCase;

class JsonProcessorServiceTest extends TestCase
{
    private JsonProcessorService $service;
    private FruitCollectionManager $fruitManager;
    private VegetableCollectionManager $vegetableManager;

    protected function setUp(): void
    {
        $this->fruitManager = $this->createMock(FruitCollectionManager::class);
        $this->vegetableManager = $this->createMock(VegetableCollectionManager::class);
        
        $this->service = new JsonProcessorService(
            $this->fruitManager,
            $this->vegetableManager
        );
    }

    public function testProcessDataWithValidFruitsAndVegetables(): void
    {
        $data = [
            [
                'name' => 'Apple',
                'type' => 'fruit',
                'quantity' => 10,
                'unit' => 'kg'
            ],
            [
                'name' => 'Carrot',
                'type' => 'vegetable',
                'quantity' => 5,
                'unit' => 'kg'
            ]
        ];

        $this->fruitManager->expects($this->once())
            ->method('add')
            ->with([
                'name' => 'Apple',
                'type' => ItemType::FRUIT->value,
                'quantity' => 10,
                'unit' => 'kg'
            ]);

        $this->vegetableManager->expects($this->once())
            ->method('add')
            ->with([
                'name' => 'Carrot',
                'type' => ItemType::VEGETABLE->value,
                'quantity' => 5,
                'unit' => 'kg'
            ]);

        $result = $this->service->processData($data);

        $this->assertEquals(1, $result['fruits_count']);
        $this->assertEquals(1, $result['vegetables_count']);
        $this->assertEmpty($result['errors']);
        $this->assertTrue($result['success']);
    }

    public function testProcessDataWithInvalidType(): void
    {
        $data = [
            [
                'name' => 'Invalid',
                'type' => 'invalid_type',
                'quantity' => 10,
                'unit' => 'kg'
            ]
        ];

        $result = $this->service->processData($data);

        $this->assertEquals(0, $result['fruits_count']);
        $this->assertEquals(0, $result['vegetables_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Invalid type', $result['errors'][0]);
        $this->assertFalse($result['success']);
    }

    public function testProcessDataWithMissingFields(): void
    {
        $data = [
            [
                'name' => 'Incomplete',
                'type' => 'fruit'
                // Missing quantity and unit
            ]
        ];

        $result = $this->service->processData($data);

        $this->assertEquals(0, $result['fruits_count']);
        $this->assertEquals(0, $result['vegetables_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Missing required fields', $result['errors'][0]);
        $this->assertFalse($result['success']);
    }

    public function testProcessDataWithManagerException(): void
    {
        $data = [
            [
                'name' => 'Apple',
                'type' => 'fruit',
                'quantity' => 10,
                'unit' => 'kg'
            ]
        ];

        $this->fruitManager->expects($this->once())
            ->method('add')
            ->willThrowException(new \Exception('Database error'));

        $result = $this->service->processData($data);

        $this->assertEquals(0, $result['fruits_count']);
        $this->assertEquals(0, $result['vegetables_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Database error', $result['errors'][0]);
        $this->assertFalse($result['success']);
    }

    public function testProcessDataWithEnumValidation(): void
    {
        $data = [
            [
                'name' => 'Apple',
                'type' => ItemType::FRUIT->value,
                'quantity' => 10,
                'unit' => 'kg'
            ],
            [
                'name' => 'Carrot',
                'type' => ItemType::VEGETABLE->value,
                'quantity' => 5,
                'unit' => 'kg'
            ]
        ];

        $this->fruitManager->expects($this->once())
            ->method('add')
            ->with($this->callback(function ($itemData) {
                return $itemData['type'] === ItemType::FRUIT->value;
            }));

        $this->vegetableManager->expects($this->once())
            ->method('add')
            ->with($this->callback(function ($itemData) {
                return $itemData['type'] === ItemType::VEGETABLE->value;
            }));

        $result = $this->service->processData($data);

        $this->assertEquals(1, $result['fruits_count']);
        $this->assertEquals(1, $result['vegetables_count']);
        $this->assertEmpty($result['errors']);
        $this->assertTrue($result['success']);
    }
} 
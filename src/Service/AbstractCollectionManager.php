<?php

namespace App\Service;

use App\DTO\AddItemDTO;
use App\Resource\FruitResource;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractCollectionManager
{
    protected string $resourceClass;
    protected string $entityClass;

    public function __construct(
        protected EntityRepository $repository,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * List items with optional filtering
     */
    public function list(array $filters = [], array $sorts = [], string $unit = 'g'): array
    {
        $criteria = [];

        // Apply filters
        if (isset($filters['type'])) {
            $criteria['type'] = $filters['type'];
        }

        if (isset($filters['name'])) {
            $criteria['name'] = $filters['name'];
        }

        $items = $this->repository->findBy($criteria);

        return $this->getResourceClass()::collection($items, $unit);
    }

    /**
     * Add a new item to the collection
     */
    public function add(array $data): array
    {
        $itemData = $this->getResourceClass()::fromRequest($data);

        // Check if item with same name already exists
        $existingItem = $this->repository->findOneBy(['name' => $itemData['name']]);
        
        if ($existingItem) {
            // Add quantities to existing item
            $quantityInGrams = $this->convertToGrams($itemData['quantity'], $data['unit'] ?? 'g');
            $newTotalQuantity = $existingItem->getQuantity() + $quantityInGrams;
            $existingItem->setQuantity($newTotalQuantity);
            
            $errors = $this->validator->validate($existingItem);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                throw new InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
            }
            
            $this->entityManager->flush();
            
            return $this->getResourceClass()::toArray($existingItem, 'g');
        }

        // Create new item if it doesn't exist
        $item = $this->createEntity();
        $item->setName($itemData['name']);
        $item->setType($itemData['type']);

        $quantityInGrams = $this->convertToGrams($itemData['quantity'], $data['unit'] ?? 'g');
        $item->setQuantity($quantityInGrams);

        $item->setUnit('g');

        $errors = $this->validator->validate($item);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new InvalidArgumentException('Validation failed: ' . implode(', ', $errorMessages));
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->getResourceClass()::toArray($item, 'g');
    }

    /**
     * Convert quantity to grams
     */
    private function convertToGrams(float $quantity, string $unit): int
    {
        return match ($unit) {
            'kg' => (int) ($quantity * 1000),
            'g' => (int) $quantity,
            default => throw new InvalidArgumentException("Unsupported unit: {$unit}")
        };
    }

    /**
     * Remove an item from the collection
     */
    public function remove(int $id): bool
    {
        $item = $this->repository->find($id);

        if (!$item) {
            throw new InvalidArgumentException($this->getEntityName() . ' not found');
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Search items by name
     */
    public function search(string $query, string $unit = 'g'): array
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Search query is required');
        }

        $items = $this->repository->createQueryBuilder('i')
            ->where('i.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        return $this->getResourceClass()::collection($items, $unit);
    }

    /**
     * Get the resource class for this collection
     */
    abstract protected function getResourceClass(): string;

    /**
     * Get the entity name for error messages
     */
    abstract protected function getEntityName(): string;

    /**
     * Create a new entity instance
     */
    abstract protected function createEntity(): object;

    /**
     * Add a new item from DTO
     */
    public function addFromDTO(AddItemDTO $dto): array
    {
        return $this->add([
            'name' => $dto->name,
            'type' => $dto->type->value,
            'quantity' => $dto->quantity,
            'unit' => $dto->unit
        ]);
    }
}

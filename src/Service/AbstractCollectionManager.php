<?php

namespace App\Service;

use App\DTO\AddItemDTO;
use App\Trait\SortableTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractCollectionManager
{
    use SortableTrait;

    protected string $entityClass;

    public function __construct(
        protected EntityRepository $repository,
        protected ValidatorInterface $validator,
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * List items with optional filtering and sorting
     */
    public function list(array $filters = [], array $sorts = [], string $unit = 'g'): array
    {
        $queryBuilder = $this->repository->createQueryBuilder('i');

        if (isset($filters['name'])) {
            $queryBuilder->andWhere('i.name = :name')
                ->setParameter('name', $filters['name']);
        }

        $this->applySorting($queryBuilder, $sorts);

        $items = $queryBuilder->getQuery()->getResult();

        return $this->getResourceClass()::collection($items, $unit);
    }

    /**
     * Add a new item to the collection
     */
    public function add(array $data): array
    {
        $existingItem = $this->repository->findOneBy(['name' => $data['name']]);

        if ($existingItem) {
            $quantityInGrams = $this->convertToGrams($data['quantity'], $data['unit'] ?? 'g');
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

        $item = $this->createEntity();
        $item->setName($data['name']);

        $quantityInGrams = $this->convertToGrams($data['quantity'], $data['unit'] ?? 'g');
        $item->setQuantity($quantityInGrams);

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
     * Search items by name with optional sorting
     */
    public function search(string $query, string $unit = 'g', array $sorts = []): array
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Search query is required');
        }

        $queryBuilder = $this->repository->createQueryBuilder('i')
            ->where('i.name LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        $this->applySorting($queryBuilder, $sorts);

        $items = $queryBuilder->getQuery()->getResult();

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

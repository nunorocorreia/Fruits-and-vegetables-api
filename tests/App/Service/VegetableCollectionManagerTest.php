<?php

namespace App\Tests\App\Service;

use App\Entity\Vegetable;
use App\Repository\VegetableRepository;
use App\Resource\VegetableResource;
use App\Service\VegetableCollectionManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VegetableCollectionManagerTest extends TestCase
{
    private VegetableCollectionManager $manager;
    private VegetableRepository $repository;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private QueryBuilder $queryBuilder;
    private AbstractQuery $query;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VegetableRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(AbstractQuery::class);

        $this->manager = new VegetableCollectionManager(
            $this->repository,
            $this->validator,
            $this->entityManager
        );
    }

    public function testAddNewVegetable(): void
    {
        $data = [
            'name' => 'Carrot',
            'type' => 'vegetable',
            'quantity' => 5,
            'unit' => 'kg'
        ];

        $vegetable = new Vegetable();
        $vegetable->setName('Carrot');
        $vegetable->setQuantity(5000); // 5kg in grams

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Carrot'])
            ->willReturn(null);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) {
                return $entity instanceof Vegetable && $entity->getName() === 'Carrot';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->add($data);

        $this->assertIsArray($result);
        $this->assertEquals('Carrot', $result['name']);
        $this->assertEquals(5000, $result['quantity']);
    }

    public function testAddExistingVegetable(): void
    {
        $data = [
            'name' => 'Carrot',
            'type' => 'vegetable',
            'quantity' => 3,
            'unit' => 'kg'
        ];

        $existingVegetable = new Vegetable();
        $existingVegetable->setName('Carrot');
        $existingVegetable->setQuantity(5000); // 5kg existing

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Carrot'])
            ->willReturn($existingVegetable);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->add($data);

        $this->assertIsArray($result);
        $this->assertEquals('Carrot', $result['name']);
        $this->assertEquals(8000, $result['quantity']); // 5kg + 3kg = 8kg
    }

    public function testListVegetables(): void
    {
        $vegetables = [
            (new Vegetable())->setName('Carrot')->setQuantity(5000),
            (new Vegetable())->setName('Broccoli')->setQuantity(3000)
        ];

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($vegetables);

        $result = $this->manager->list();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Carrot', $result[0]['name']);
        $this->assertEquals('Broccoli', $result[1]['name']);
    }

    public function testSearchVegetables(): void
    {
        $vegetables = [
            (new Vegetable())->setName('Carrot')->setQuantity(5000)
        ];

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->with('i.name LIKE :query')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('query', '%Carrot%')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('i.id', 'ASC')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($vegetables);

        $result = $this->manager->search('Carrot');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Carrot', $result[0]['name']);
    }

    public function testRemoveVegetable(): void
    {
        $vegetable = new Vegetable();
        $vegetable->setName('Carrot');

        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($vegetable);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($vegetable);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->remove(1);

        $this->assertTrue($result);
    }

    public function testRemoveNonExistentVegetable(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('vegetable not found');

        $this->manager->remove(999);
    }

    public function testConvertToGrams(): void
    {
        // Test kg to grams conversion
        $data = [
            'name' => 'Carrot',
            'type' => 'vegetable',
            'quantity' => 1.5,
            'unit' => 'kg'
        ];

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Carrot'])
            ->willReturn(null);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->add($data);

        $this->assertEquals(1500, $result['quantity']); // 1.5kg = 1500g
    }

    public function testListWithUnitConversion(): void
    {
        $vegetables = [
            (new Vegetable())->setName('Carrot')->setQuantity(5000) // 5kg in grams
        ];

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getResult')
            ->willReturn($vegetables);

        $result = $this->manager->list([], [], 'kg');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(5, $result[0]['quantity']); // Should be 5kg
        $this->assertEquals('kg', $result[0]['unit']);
    }
} 
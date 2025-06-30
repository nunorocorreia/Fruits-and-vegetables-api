<?php

namespace App\Tests\App\Service;

use App\Entity\Fruit;
use App\Repository\FruitRepository;
use App\Resource\FruitResource;
use App\Service\FruitCollectionManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FruitCollectionManagerTest extends TestCase
{
    private FruitCollectionManager $manager;
    private FruitRepository $repository;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private QueryBuilder $queryBuilder;
    private AbstractQuery $query;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(FruitRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(AbstractQuery::class);

        $this->manager = new FruitCollectionManager(
            $this->repository,
            $this->validator,
            $this->entityManager
        );
    }

    public function testAddNewFruit(): void
    {
        $data = [
            'name' => 'Apple',
            'type' => 'fruit',
            'quantity' => 10,
            'unit' => 'kg'
        ];

        $fruit = new Fruit();
        $fruit->setName('Apple');
        $fruit->setQuantity(10000); // 10kg in grams

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Apple'])
            ->willReturn(null);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($entity) {
                return $entity instanceof Fruit && $entity->getName() === 'Apple';
            }));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->add($data);

        $this->assertIsArray($result);
        $this->assertEquals('Apple', $result['name']);
        $this->assertEquals(10000, $result['quantity']);
    }

    public function testAddExistingFruit(): void
    {
        $data = [
            'name' => 'Apple',
            'type' => 'fruit',
            'quantity' => 5,
            'unit' => 'kg'
        ];

        $existingFruit = new Fruit();
        $existingFruit->setName('Apple');
        $existingFruit->setQuantity(10000); // 10kg existing

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Apple'])
            ->willReturn($existingFruit);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->add($data);

        $this->assertIsArray($result);
        $this->assertEquals('Apple', $result['name']);
        $this->assertEquals(15000, $result['quantity']); // 10kg + 5kg = 15kg
    }

    public function testListFruits(): void
    {
        $fruits = [
            (new Fruit())->setName('Apple')->setQuantity(10000),
            (new Fruit())->setName('Banana')->setQuantity(5000)
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
            ->willReturn($fruits);

        $result = $this->manager->list();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Apple', $result[0]['name']);
        $this->assertEquals('Banana', $result[1]['name']);
    }

    public function testListFruitsWithFilters(): void
    {
        $fruits = [
            (new Fruit())->setName('Apple')->setQuantity(10000)
        ];

        $this->repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('i')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with('i.name = :name')
            ->willReturnSelf();

        $this->queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('name', 'Apple')
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
            ->willReturn($fruits);

        $result = $this->manager->list(['name' => 'Apple']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Apple', $result[0]['name']);
    }

    public function testSearchFruits(): void
    {
        $fruits = [
            (new Fruit())->setName('Apple')->setQuantity(10000)
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
            ->with('query', '%Apple%')
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
            ->willReturn($fruits);

        $result = $this->manager->search('Apple');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Apple', $result[0]['name']);
    }

    public function testSearchFruitsWithEmptyQuery(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Search query is required');

        $this->manager->search('');
    }

    public function testRemoveFruit(): void
    {
        $fruit = new Fruit();
        $fruit->setName('Apple');

        $this->repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($fruit);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($fruit);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->remove(1);

        $this->assertTrue($result);
    }

    public function testRemoveNonExistentFruit(): void
    {
        $this->repository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('fruit not found');

        $this->manager->remove(999);
    }

    public function testConvertToGrams(): void
    {
        // Test kg to grams conversion
        $data = [
            'name' => 'Apple',
            'type' => 'fruit',
            'quantity' => 2.5,
            'unit' => 'kg'
        ];

        $fruit = new Fruit();
        $fruit->setName('Apple');

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'Apple'])
            ->willReturn(null);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())
            ->method('persist');
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->manager->add($data);

        $this->assertEquals(2500, $result['quantity']); // 2.5kg = 2500g
    }

    public function testValidationFailure(): void
    {
        $data = [
            'name' => '',
            'type' => 'fruit',
            'quantity' => 10,
            'unit' => 'kg'
        ];

        $fruit = new Fruit();
        $fruit->setName('');

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => ''])
            ->willReturn(null);

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->manager->add($data);
    }
} 
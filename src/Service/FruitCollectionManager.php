<?php

namespace App\Service;

use App\Entity\Fruit;
use App\Repository\FruitRepository;
use App\Resource\FruitResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FruitCollectionManager extends AbstractCollectionManager
{
    public function __construct(
        FruitRepository $repository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($repository, $validator, $entityManager);
    }

    protected function getResourceClass(): string
    {
        return FruitResource::class;
    }

    protected function getEntityName(): string
    {
        return 'Fruit';
    }

    protected function createEntity(): object
    {
        return new Fruit();
    }
} 
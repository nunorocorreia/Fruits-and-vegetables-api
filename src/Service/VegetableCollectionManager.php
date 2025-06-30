<?php

namespace App\Service;

use App\Entity\Vegetable;
use App\Repository\VegetableRepository;
use App\Resource\VegetableResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Enum\ItemType;

class VegetableCollectionManager extends AbstractCollectionManager
{
    public function __construct(
        VegetableRepository $repository,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($repository, $validator, $entityManager);
    }

    protected function getResourceClass(): string
    {
        return VegetableResource::class;
    }

    protected function getEntityName(): string
    {
        return ItemType::VEGETABLE->value;
    }

    protected function createEntity(): object
    {
        return new Vegetable();
    }
} 
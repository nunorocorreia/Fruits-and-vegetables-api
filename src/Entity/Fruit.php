<?php

namespace App\Entity;

use App\Repository\FruitRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FruitRepository::class)]
class Fruit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $dateAdd = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTimeInterface $dateUpd = null;

    public function __construct()
    {
        $this->dateAdd = new DateTime();
        $this->dateUpd = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        $this->updateDateUpd();
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->updateDateUpd();
        return $this;
    }

    public function getDateAdd(): ?DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTimeInterface $dateAdd): static
    {
        $this->dateAdd = $dateAdd;
        return $this;
    }

    public function getDateUpd(): ?DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(DateTimeInterface $dateUpd): static
    {
        $this->dateUpd = $dateUpd;
        return $this;
    }

    private function updateDateUpd(): void
    {
        $this->dateUpd = new DateTime();
    }
}

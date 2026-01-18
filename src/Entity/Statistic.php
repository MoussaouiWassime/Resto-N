<?php

namespace App\Entity;

use App\Repository\StatisticRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatisticRepository::class)]
class Statistic
{
    public const INCOME = 'CA_JOURNALIER';
    public const VISITS = 'NB_VISITES';
    public const ORDERS = 'NB_COMMANDES';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $statisticType = null;

    #[ORM\Column(nullable: true)]
    private ?int $value = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'statistics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Restaurant $restaurant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatisticType(): ?string
    {
        return $this->statisticType;
    }

    public function setStatisticType(string $statisticType): static
    {
        $this->statisticType = $statisticType;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }
}

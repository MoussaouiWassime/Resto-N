<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 150)]
    private ?string $address = null;

    #[ORM\Column(length: 5)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 150)]
    private ?string $city = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $openingTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $closingTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $category = null;

    #[ORM\Column]
    private ?bool $darkKitchen = null;

    /**
     * @var Collection<int, Statistic>
     */
    #[ORM\OneToMany(targetEntity: Statistic::class, mappedBy: 'restaurant')]
    private Collection $statistics;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'restaurant')]
    private Collection $reservations;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'restaurant', orphanRemoval: true)]
    private Collection $orders;

    /**
     * @var Collection<int, Stock>
     */
    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'restaurant')]
    private Collection $stocks;

    public function __construct()
    {
        $this->statistics = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->stocks = new ArrayCollection();
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

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getOpeningTime(): ?\DateTime
    {
        return $this->openingTime;
    }

    public function setOpeningTime(?\DateTime $openingTime): static
    {
        $this->openingTime = $openingTime;

        return $this;
    }

    public function getClosingTime(): ?\DateTime
    {
        return $this->closingTime;
    }

    public function setClosingTime(\DateTime $closingTime): static
    {
        $this->closingTime = $closingTime;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function isDarkKitchen(): ?bool
    {
        return $this->darkKitchen;
    }

    public function setDarkKitchen(bool $darkKitchen): static
    {
        $this->darkKitchen = $darkKitchen;

        return $this;
    }

    /**
     * @return Collection<int, Statistic>
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }

    public function addStatistic(Statistic $statistic): static
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics->add($statistic);
            $statistic->setRestaurant($this);
        }

        return $this;
    }

    public function removeStatistic(Statistic $statistic): static
    {
        if ($this->statistics->removeElement($statistic)) {
            // set the owning side to null (unless already changed)
            if ($statistic->getRestaurant() === $this) {
                $statistic->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setRestaurant($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getRestaurant() === $this) {
                $reservation->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setRestaurant($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getRestaurant() === $this) {
                $order->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getStocks(): Collection
    {
        return $this->stocks;
    }

    public function addStock(Stock $stock): static
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks->add($stock);
            $stock->setRestaurant($this);
        }

        return $this;
    }

    public function removeStock(Stock $stock): static
    {
        if ($this->stocks->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getRestaurant() === $this) {
                $stock->setRestaurant(null);
            }
        }

        return $this;
    }
}

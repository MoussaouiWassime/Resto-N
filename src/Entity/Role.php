<?php

namespace App\Entity;

use App\Enum\RestaurantRole;
use App\Repository\RoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1, enumType: RestaurantRole::class)]
    private ?RestaurantRole $role = null;
    #[ORM\ManyToOne(inversedBy: 'roles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Restaurant $restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'restaurantRoles')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invitationToken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?RestaurantRole
    {
        return $this->role;
    }

    public function setRole(RestaurantRole $role): static
    {
        $this->role = $role;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getInvitationToken(): ?string
    {
        return $this->invitationToken;
    }

    public function setInvitationToken(?string $invitationToken): static
    {
        $this->invitationToken = $invitationToken;

        return $this;
    }

    public function isOwner(): bool
    {
        return $this->role === RestaurantRole::OWNER;
    }
}
